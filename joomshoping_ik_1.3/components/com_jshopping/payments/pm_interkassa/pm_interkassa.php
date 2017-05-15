<?php
/* Создано в компании www.gateon.net
 * =================================================================
 * Модуль оплаты Интеркасса 2.0 для Joomla 3.5.x + JoomShopping 4.x
 * ПРИМЕЧАНИЕ ПО ИСПОЛЬЗОВАНИЮ
 * =================================================================
 *  Этот файл предназначен для Joomla 3.5.x и выше
 *  www.gateon.net не гарантирует правильную работу этого расширения
 *  для более ранних версий Joomla
 * =================================================================
*/
defined('_JEXEC') or die('Restricted access');

class pm_interkassa extends PaymentRoot
{
    
    private $ip_stack = array(
        'ip_begin' => '151.80.190.97',
        'ip_end'   => '151.80.190.104'
    );

    function showPaymentForm($params, $pmconfigs)
    {
        include(dirname(__FILE__)."/paymentform.php");
    }

    function showAdminFormParams($params)
    {
        $jmlThisDocument = & JFactory::getDocument();
        $array_params = array('secret_key', 'secret_test_key', 'wallet_id', 'transaction_end_status', 'transaction_pending_status', 'transaction_failed_status');
        foreach ($array_params as $key)
            if (!isset($params[$key])) 
                $params[$key] = '';
        $orders = JSFactory::getModel('orders', 'JshoppingModel');
        include(dirname(__FILE__)."/adminparamsform.php");  
    }


    function checkTransaction($pmconfigs, $order, $act)
    {
         if(!ip2long($_SERVER['REMOTE_ADDR'])>=ip2long($this->ip_stack['ip_begin']) && !ip2long($_SERVER['REMOTE_ADDR'])<=ip2long($this->ip_stack['ip_end'])){
            return array(0, 'Hacking attempt!.');
            exit();
            }

            $jshopConfig = JSFactory::getConfig();
            saveToLog("paymentdata.log", "start cheking!!!");

            $merchant_id = $pmconfigs['wallet_id'];

            if(isset($_POST['ik_pw_via']) && $_POST['ik_pw_via'] === 'test_interkassa_test_xts'){
                $key_sign = $pmconfigs['secret_test_key'];
            } else {
                $key_sign = $pmconfigs['secret_key'];
            }
            $data = array();
            foreach ($_REQUEST as $key => $value) {
                if (!preg_match('/ik_/', $key)) continue;
                $data[$key] = $value;
            }
            $ik_sign = $data['ik_sign'];
            unset($data['ik_sign']);
            ksort($data, SORT_STRING);
            array_push($data, $key_sign);
            $signString = implode(':', $data);
            $sign = base64_encode(md5($signString, true));

            if($sign === $ik_sign && $data['ik_co_id'] === $merchant_id){
                $order_id = $data['ik_pm_no'];
                if($data['ik_inv_st'] == 'success'){
                    return array(1, 'Заказ #'.$order_id.' успешно оплачен с помощью "Интеркассы"');                
                } else {
                    return array(4, 'Платеж по заказу: #'.$order_id.'. был отменен!');
                }
            } else {
                return array(0, 'Error signature.');
            }
        
    }

    function showEndForm($pmconfigs, $order)
    {
    $jshopConfig = &JSFactory::getConfig();        
        
    $action_url = "https://sci.interkassa.com/";
    $order_id = $order->order_id;

    //params set
    $params = array(
        'ik_am' => number_format($order->order_total, 2, ".", ""),
        'ik_cur' => $order->currency_code_iso,
        'ik_co_id' => $pmconfigs['wallet_id'],
        'ik_pm_no' => $order_id,
        'ik_desc' => "#$order_id",
        'ik_exp' => date("Y-m-d H:i:s", time() + 24 * 3600),
        'ik_suc_u' => $pmconfigs['success_url'],
        'ik_fal_u' => $pmconfigs['fail_url'],
        'ik_ia_u' => $pmconfigs['notify_url'],
    );

    ksort($params, SORT_STRING);
    if (isset($params['test_mode'])) {
        $params['secret'] = $pmconfigs['test_key'];
    } else {
        $params['secret'] = $pmconfigs['secret_key'];
    }
    $signString = implode(':', $params);

    $signature = base64_encode(md5($signString, true));
    unset($params["secret"]);
    $params["ik_sign"] = $signature;
    
?>
    <html>
    <head>
    <meta http-equiv="content-type" content="text/html; charset=UTF-8" />          
    </head>        
    <body>
    <form id="paymentform" action="<?php print $action_url?>" name = "paymentform" method = "POST">
        <input type=hidden name="ik_am" value='<?php print $params['ik_am']; ?>'>
        <input type=hidden name="ik_co_id" value='<?php print $params['ik_co_id']; ?>'>
        <input type=hidden name="ik_pm_no" value='<?php print $params['ik_pm_no']; ?>'>
        <input type=hidden name="ik_cur" value='<?php print $params['ik_cur']; ?>'>
        <input type=hidden name="ik_desc" value='<?php print $params['ik_desc']; ?>'>
        <input type=hidden name="ik_exp" value='<?php print $params['ik_exp']; ?>'>
        <input type=hidden name="ik_suc_u" value='<?php print $params['ik_suc_u']; ?>'>
        <input type=hidden name="ik_fal_u" value='<?php print $params['ik_fal_u']; ?>'>
        <input type=hidden name="ik_ia_u" value='<?php print $params['ik_ia_u']; ?>'>
        <input type=hidden name="ik_sign" value='<?php print $params['ik_sign']; ?>'>
    </form>        
        <?php print _JSHOP_REDIRECT_TO_PAYMENT_PAGE ?>
        <br>

    <script type="text/javascript">console.log(<?php echo $order->currency_code_iso; ?>);</script>
        <script type="text/javascript">
            document.getElementById('paymentform').submit();
        </script>
        </body>
        </html>
        <?php
        die();
    }

    function getUrlParams($pmconfigs){
        $params = array();
        $params['order_id'] = JFactory::getApplication()->input->getInt("ik_pm_no");
        $params['hash'] = "";
        $params['checkHash'] = 0;
        $params['checkReturnParams'] = 0;
        return $params;
    }

}
?>