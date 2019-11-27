<?php
//Модуль разработан в компании GateOn предназначен для CMS Joomla 3.5 + VirtueMart 3.0.x 
//Сайт разработчикa: www.gateon.net
//E-mail: www@smartbyte.pro
//Версия: 1.2


    


if (!defined('_VALID_MOS') && !defined('_JEXEC')){
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');
}

if (!class_exists ('vmPSPlugin')) {
	require(JPATH_VM_PLUGINS . DS . 'vmpsplugin.php');
}

class plgVmPaymentInterkassa extends vmPSPlugin
{   
    function __construct(&$subject, $config)
    {
        parent::__construct($subject, $config);  
		
        $this->_loggable   = true;
        $this->tableFields = array_keys($this->getTableSQLFields());
		$this->_tablepkey = 'id'; 
		$this->_tableId = 'id'; 
		$varsToPush = $this->getVarsToPush();
	
        $this->setConfigParameterable($this->_configTableFieldName, $varsToPush);

    }    
    
    protected function getVmPluginCreateTableSQL()
    {
        return $this->createTableSQL('Payment Interkassa Table');
    }
    
    function getTableSQLFields()
    {
        $SQLfields = array(
            'id' 							=> 'int(1) UNSIGNED NOT NULL AUTO_INCREMENT',
            'virtuemart_order_id' 			=> 'int(11) UNSIGNED',
            'order_number' 					=> 'char(32)',
            'virtuemart_paymentmethod_id' 	=> 'mediumint(1) UNSIGNED',
            'payment_name' 					=> 'varchar(5000)',
            'payment_order_total' 			=> 'decimal(15,2) NOT NULL DEFAULT \'0.00\' ',
            'payment_currency' 				=> 'char(3) '	
        );
        
        return $SQLfields;
    }

    function plgVmConfirmedOrder($cart, $order)
    {
        if (!($method = $this->getVmPluginMethod($order['details']['BT']->virtuemart_paymentmethod_id))) {
            return null;
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }
        
        $lang     = JFactory::getLanguage();
        $filename = 'com_virtuemart';
        $lang->load($filename, JPATH_ADMINISTRATOR);
        $vendorId = 0;
        
        $session        = JFactory::getSession();
        $return_context = $session->getId();
        $this->logInfo('plgVmConfirmedOrder order number: ' . $order['details']['BT']->order_number, 'message');
        
        $html = "";
        
        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
        if (!$method->payment_currency)
            $this->getPaymentCurrency($method);

        // получение кода валюты вида "RUB"
        $q = 'SELECT `currency_code_3` FROM `#__virtuemart_currencies` WHERE `virtuemart_currency_id`="' . $method->payment_currency . '" ';
        $db =& JFactory::getDBO();
        $db->setQuery($q);

        $currency = $db->loadResult();

        $dateexp = date("Y-m-d H:i:s", time() + 24 * 3600);
        $amount = ceil($order['details']['BT']->order_total*100)/100;
        $virtuemart_order_id = VirtueMartModelOrders::getOrderIdByOrderNumber($order['details']['BT']->order_number);
        
        $desc = 'Оплата заказа №'.$order['details']['BT']->order_number;

        $action_url = "https://sci.interkassa.com/"; 
        $this->_virtuemart_paymentmethod_id      = $order['details']['BT']->virtuemart_paymentmethod_id;
        $dbValues['payment_name']                = $this->renderPluginName($method);
        $dbValues['order_number']                = $order['details']['BT']->order_number;
        $dbValues['virtuemart_paymentmethod_id'] = $this->_virtuemart_paymentmethod_id;
        $dbValues['payment_currency']            = $currency;
        $dbValues['payment_order_total']         = $amount;
        $this->storePSPluginInternalData($dbValues);
        $success_url = JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=orders&layout=details&order_number=' . $order['details']['BT']->order_number . '&order_pass=' . $order['details']['BT']->order_pass);
        $fail_url = JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginUserPaymentCancel&on=' . $order['details']['BT']->order_number . '&pm=' . $order['details']['BT']->virtuemart_paymentmethod_id);
        $interaction_url = JROUTE::_(JURI::root().'index.php?option=com_virtuemart&view=pluginresponse&task=pluginnotification&pro=1&tmpl=component');

        $params = array(
            'ik_am' => $amount,
            'ik_cur' => $currency,
            'ik_co_id' => $method->merchant_id,
            'ik_pm_no' => $virtuemart_order_id,
            'ik_desc' => "#$virtuemart_order_id",
            'ik_suc_u' => $success_url,
            'ik_fal_u' => $fail_url,
            'ik_pnd_u' => $success_url,
            'ik_ia_u' => $interaction_url,
            'ik_exp' => date("Y-m-d H:i:s", time() + 24 * 3600)
        );

        ksort($params, SORT_STRING);
        $params['secret'] = $method->secret_key;
        $signString = implode(':', $params);

        $signature = base64_encode(md5($signString, true));
        unset($params["secret"]);

		$html = '<form action='.$action_url.' method="POST"  name="vm_interkassa_form" id="ikform">
		            <input type="hidden" value="'.$amount.'" name="ik_am">
					<input type="hidden" value="'.$method->merchant_id.'" name="ik_co_id">					
					<input type="hidden" value="'.$virtuemart_order_id.'" name="ik_pm_no">
					<input type="hidden" value="'.$params['ik_desc'].'" name="ik_desc">
					<input type="hidden" value="'.$currency.'" name="ik_cur">
					<input type="hidden" value="'.$dateexp.'" name="ik_exp">					
					<input type="hidden" value="'.$signature.'" name="ik_sign">					
                    <input type="hidden" value="'.$success_url.'" name="ik_suc_u">                  
					<input type="hidden" value="'.$success_url.'" name="ik_pnd_u">					
                    <input type="hidden" value="'.$interaction_url.'" name="ik_ia_u">                 
                    <input type="hidden" value="'.$fail_url.'" name="ik_fal_u">                 				
				</form>
			     <button onclick="document.forms.vm_interkassa_form.submit()" class="btn btn-primary">Подтвердить</button>
				';
        if($method->api_status == 1){
            $_SESSION['virtuemart_paymentmethod_id'] = $order['details']['BT']->virtuemart_paymentmethod_id;
            $img_path = JURI::base(). "plugins/vmpayment/interkassa/paysystems/";
            $payment_systems = $this->getIkPaymentSystems($method->merchant_id, $method->api_id, $method->api_key);
           $html .=  require 'api.tpl.php';
        }
        
        return $this->processConfirmedOrderPaymentResponse(true, $cart, $order, $html, $this->renderPluginName($method, $order), 'P');
        }
         function plgVmOnSelfCallFE ($type, $name, &$render){
             $method = $this->getVmPluginMethod($_SESSION['virtuemart_paymentmethod_id']);
            if ($name != $this->_name || $type != 'vmpayment') return false;
            
            $params = array();
            parse_str($_POST['form'], $params);

             $render->sign = $this->IkSignFormation($params, $method->secret_key);
        }

        public function IkSignFormation($data, $secret_key){
            if (!empty($data['ik_sign'])) unset($data['ik_sign']);

            $dataSet = array();
            foreach ($data as $key => $value) {
                if (!preg_match('/ik_/', $key)) continue;
                $dataSet[$key] = $value;
            }

            ksort($dataSet, SORT_STRING);
            array_push($dataSet, $secret_key);
            $arg = implode(':', $dataSet);
            $ik_sign = base64_encode(md5($arg, true));

            return $ik_sign;
        }

        public function getIkPaymentSystems($ik_cashbox_id, $ik_api_id, $ik_api_key)
        {
            $username = $ik_api_id;
            $password = $ik_api_key;
            $remote_url = 'https://api.interkassa.com/v1/' . 'paysystem-input-payway?checkoutId=' . $ik_cashbox_id;

            $businessAcc = $this->getIkBusinessAcc($username, $password);

            $ikHeaders = [];
            $ikHeaders[] = "Authorization: Basic " . base64_encode("$username:$password");
            if (!empty($businessAcc)) {
                $ikHeaders[] = "Ik-Api-Account-Id: " . $businessAcc;
            }

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $remote_url);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
            curl_setopt($ch, CURLOPT_HEADER, false);
            curl_setopt($ch, CURLOPT_HTTPHEADER, $ikHeaders);
            $response = curl_exec($ch);

            $json_data = json_decode($response);

            if (empty($json_data))
                return '<strong style="color:red;">Error!!! System response empty!</strong>';

            if ($json_data->status != 'error') {
                $payment_systems = array();
                if (!empty($json_data->data)) {
                    foreach ($json_data->data as $ps => $info) {
                        $payment_system = $info->ser;
                        if (!array_key_exists($payment_system, $payment_systems)) {
                            $payment_systems[$payment_system] = array();
                            foreach ($info->name as $name) {
                                if ($name->l == 'en') {
                                    $payment_systems[$payment_system]['title'] = ucfirst($name->v);
                                }
                                $payment_systems[$payment_system]['name'][$name->l] = $name->v;
                            }
                        }
                        $payment_systems[$payment_system]['currency'][strtoupper($info->curAls)] = $info->als;
                    }
                }

                return !empty($payment_systems) ? $payment_systems : '<strong style="color:red;">API connection error or system response empty!</strong>';
            } else {
                if (!empty($json_data->message))
                    return '<strong style="color:red;">API connection error!<br>' . $json_data->message . '</strong>';
                else
                    return '<strong style="color:red;">API connection error or system response empty!</strong>';
            }
        }

        public function getIkBusinessAcc($username = '', $password = '')
        {
            $tmpLocationFile = __DIR__ . '/tmpLocalStorageBusinessAcc.ini';
            $dataBusinessAcc = function_exists('file_get_contents') ? file_get_contents($tmpLocationFile) : '{}';
            $dataBusinessAcc = json_decode($dataBusinessAcc, 1);
            $businessAcc = is_string($dataBusinessAcc['businessAcc']) ? trim($dataBusinessAcc['businessAcc']) : '';
            if (empty($businessAcc) || sha1($username . $password) !== $dataBusinessAcc['hash']) {
                $curl = curl_init();
                curl_setopt($curl, CURLOPT_URL, 'https://api.interkassa.com/v1/' . 'account');
                curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
                curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($curl, CURLOPT_FOLLOWLOCATION, false);
                curl_setopt($curl, CURLOPT_HEADER, false);
                curl_setopt($curl, CURLOPT_HTTPHEADER, ["Authorization: Basic " . base64_encode("$username:$password")]);
                $response = curl_exec($curl);

                if (!empty($response['data'])) {
                    foreach ($response['data'] as $id => $data) {
                        if ($data['tp'] == 'b') {
                            $businessAcc = $id;
                            break;
                        }
                    }
                }

                if (function_exists('file_put_contents')) {
                    $updData = [
                        'businessAcc' => $businessAcc,
                        'hash' => sha1($username . $password)
                    ];
                    file_put_contents($tmpLocationFile, json_encode($updData, JSON_PRETTY_PRINT));
                }

                return $businessAcc;
            }

            return $businessAcc;
        }
    function plgVmOnShowOrderBEPayment($virtuemart_order_id, $virtuemart_payment_id)
    {
        if (!$this->selectedThisByMethodId($virtuemart_payment_id)) {
            return null; // Another method was selected, do nothing
        }
        
        $db = JFactory::getDBO();
        $q  = 'SELECT * FROM `' . $this->_tablename . '` ' . 'WHERE `virtuemart_order_id` = ' . $virtuemart_order_id;
        $db->setQuery($q);
        if (!($paymentTable = $db->loadObject())) {
            vmWarn(500, $q . " " . $db->getErrorMsg());
            return '';
        }
        $this->getPaymentCurrency($paymentTable);
        
        $html = '<table class="adminlist">' . "\n";
        $html .= $this->getHtmlHeaderBE();
        $html .= $this->getHtmlRowBE('STANDARD_PAYMENT_NAME', $paymentTable->payment_name);
        $html .= $this->getHtmlRowBE('STANDARD_PAYMENT_TOTAL_CURRENCY', $paymentTable->payment_order_total . ' ' . $paymentTable->payment_currency);
        $html .= '</table>' . "\n";
        return $html;
    }
    
    function getCosts(VirtueMartCart $cart, $method, $cart_prices)
    {
        return 0;
    }
    
    protected function checkConditions($cart, $method, $cart_prices)
    {
        return true;
    }
    
    function plgVmOnStoreInstallPaymentPluginTable($jplugin_id)
    {
        return $this->onStoreInstallPluginTable($jplugin_id);
    }
    
    public function plgVmOnSelectCheckPayment(VirtueMartCart $cart)
    {
        return $this->OnSelectCheck($cart);
    }
    
    public function plgVmDisplayListFEPayment(VirtueMartCart $cart, $selected = 0, &$htmlIn)
    {
        return $this->displayListFE($cart, $selected, $htmlIn);
    }
    
    public function plgVmonSelectedCalculatePricePayment(VirtueMartCart $cart, array &$cart_prices, &$cart_prices_name)
    {
        return $this->onSelectedCalculatePrice($cart, $cart_prices, $cart_prices_name);
    }
    
    function plgVmgetPaymentCurrency($virtuemart_paymentmethod_id, &$paymentCurrencyId)
    {
        if (!($method = $this->getVmPluginMethod($virtuemart_paymentmethod_id))) {
            return null; // Another method was selected, do nothing
        }
        if (!$this->selectedThisElement($method->payment_element)) {
            return false;
        }
        $this->getPaymentCurrency($method);
        
        $paymentCurrencyId = $method->payment_currency;
    }
    
    function plgVmOnCheckAutomaticSelectedPayment(VirtueMartCart $cart, array $cart_prices = array())
    {
        return $this->onCheckAutomaticSelected($cart, $cart_prices);
    }
    
    public function plgVmOnShowOrderFEPayment($virtuemart_order_id, $virtuemart_paymentmethod_id, &$payment_name)
    {
        $this->onShowOrderFE($virtuemart_order_id, $virtuemart_paymentmethod_id, $payment_name);
    }
    
    function plgVmonShowOrderPrintPayment($order_number, $method_id)
    {
        return $this->onShowOrderPrint($order_number, $method_id);
    }    
    
    function plgVmDeclarePluginParamsPaymentVM3( &$data) 
	{
		return $this->declarePluginParams('payment', $data);
	}
    function plgVmSetOnTablePluginParamsPayment($name, $id, &$table)
    {
        return $this->setOnTablePluginParams($name, $id, $table);
    }
    
    
    public function plgVmOnPaymentNotification()
    {	
       // $this->wrlog('Hello');
        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');

        $orderid = $_POST['ik_pm_no'];
        $payment = $this->getDataByOrderId($orderid);
        $method = $this->getVmPluginMethod($payment->virtuemart_paymentmethod_id);        
        $amount = ceil($payment->payment_order_total*100)/100;

        if ($method){      

            if (count($_POST) && $this->checkIP() && isset($_POST['ik_sign'])) {
               
	            if ($_POST['ik_inv_st'] == 'success' && $method->merchant_id == $_POST['ik_co_id'] ) {  
	                if(isset($_REQUEST['ik_pw_via']) && $_REQUEST['ik_pw_via'] == 'test_interkassa_test_xts'){
	                    $secret_key = $method->test_key;
	                } else {
	                    $secret_key = $method->secret_key;
	                }

	                $request = $_POST;
	                $request_sign = $request['ik_sign'];
	                unset($request['ik_sign']);

	                //удаляем все поле которые не принимают участия в формировании цифровой подписи
	                foreach ($request as $key => $value) {
	                    if (!preg_match('/ik_/', $key)) continue;
	                    $request[$key] = $value;
	                }

	                //формируем цифровую подпись
	                ksort($request, SORT_STRING);
	                array_push($request, $secret_key);
	                $str = implode(':', $request);
	                $sign = base64_encode(md5($str, true));
	                if ($request_sign == $sign) {
	                    $order['order_status'] = $method->status_success;
	                    $order['virtuemart_order_id'] = $orderid;
	                    $order['customer_notified'] = 1;
	                    $order['comments'] = JTExt::sprintf('INTERKASSA_PAYMENT_CONFIRMED', $payment->order_number);
	                    $modelOrder = VmModel::getModel('orders');
	                    $modelOrder->updateStatusForOneOrder($orderid, $order, true);
	                } else {
		                $order['order_status']        = $method->status_pending;
		                $order['virtuemart_order_id'] = $orderid;
		                $order['customer_notified']   = 0;
		                $order['comments']            = JTExt::sprintf('INTERKASSA_STATUS_FAILED', $payment->order_number);
		                $modelOrder = VmModel::getModel ('orders');
		                $modelOrder->updateStatusForOneOrder($orderid, $order, true);
	                } 
	            }           
    		} else {
	    		exit;
	        	return null;
    		}
		} else {
            exit;
            return null;
        }
    } 
    
    function plgVmOnUserPaymentCancel()
    {
        if (!class_exists('VirtueMartModelOrders'))
            require(JPATH_VM_ADMINISTRATOR . DS . 'models' . DS . 'orders.php');
        
        $order_number = JRequest::getVar('on');
        if (!$order_number)
            return false;
        $db    = JFactory::getDBO();
        $query = 'SELECT ' . $this->_tablename . '.`virtuemart_order_id` FROM ' . $this->_tablename . " WHERE  `order_number`= '" . $order_number . "'";
        
        $db->setQuery($query);
        $virtuemart_order_id = $db->loadResult();
        
        if (!$virtuemart_order_id) {
            return null;
        }
        $this->handlePaymentUserCancel($virtuemart_order_id);
        
        return true;
    }

    function wrlog($content){
        $file = $_SERVER['DOCUMENT_ROOT'].'/logs/log.txt';
        $doc = fopen($file, 'a');
   
        file_put_contents($file, PHP_EOL . $content, FILE_APPEND);
        fclose($doc);
       
    }
    
    
 	function checkIP(){
	    $ip_stack = array(
	        'ip_begin'=>'151.80.190.97',
	        'ip_end'=>'151.80.190.104'
	    );

	    if(ip2long($_SERVER['REMOTE_ADDR'])<ip2long($ip_stack['ip_begin']) || ip2long($_SERVER['REMOTE_ADDR'])>ip2long($ip_stack['ip_end'])){
	        $this->wrlog('REQUEST IP'.$_SERVER['REMOTE_ADDR'].'doesnt match');
	        die('Ты мошенник! Пшел вон отсюда!');
	    }
	    return true;
    }
}
