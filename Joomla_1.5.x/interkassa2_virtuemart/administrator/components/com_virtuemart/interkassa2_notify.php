<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Joomla 1.5.26 + VirtueMart 1.1.9
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.0
 */


//Здесь происходит какая-то дичь
//мы вроде как получаем все конфиги, кроме того что нам нужен,по-этому подключаем его после всего ЭТОГО

//НАЧАЛО ЭТОГО
global $mosConfig_absolute_path, $mosConfig_live_site, $mosConfig_lang, $database, $mosConfig_mailfrom, $mosConfig_fromname;

$my_path = dirname(__FILE__);

//Получаем конфиги Джумлы
if (file_exists($my_path . "/../../../configuration.php")) {
	$absolute_path = dirname($my_path . "/../../../configuration.php");
	require_once ($my_path . "/../../../configuration.php");
} elseif (file_exists($my_path . "/../../configuration.php")) {
	$absolute_path = dirname($my_path . "/../../configuration.php");
	require_once ($my_path . "/../../configuration.php");
} elseif (file_exists($my_path . "/configuration.php")) {
	$absolute_path = dirname($my_path . "/configuration.php");
	require_once ($my_path . "/configuration.php");
} else {
	die("Joomla Configuration File not found!");
}

$absolute_path = realpath($absolute_path);


//Подключаем подходящий CMS фреймворк
if (class_exists('jconfig')) {
	define('_JEXEC', 1);
	define('JPATH_BASE', $absolute_path);
	define('DS', DIRECTORY_SEPARATOR);

	require_once (JPATH_BASE . DS . 'includes' . DS . 'defines.php');
	require_once (JPATH_BASE . DS . 'includes' . DS . 'framework.php');

	$mainframe = & JFactory::getApplication('site');
	$mainframe->initialise();
	JPluginHelper::importPlugin('system');
	$mainframe->triggerEvent('onBeforeStart');
	$lang = & JFactory::getLanguage();
	$mosConfig_lang = $GLOBALS['mosConfig_lang'] = strtolower($lang->getBackwardLang());
	$mosConfig_live_site = str_replace('/administrator/components/com_virtuemart', '', JURI::base());
	$mosConfig_absolute_path = JPATH_BASE;
} else {
	define('_VALID_MOS', '1');
	require_once ($mosConfig_absolute_path . '/includes/joomla.php');
	require_once ($mosConfig_absolute_path . '/includes/database.php');
	$database = new database($mosConfig_host, $mosConfig_user, $mosConfig_password, $mosConfig_db, $mosConfig_dbprefix);
	$mainframe = new mosMainFrame($database, 'com_virtuemart', $mosConfig_absolute_path);
}

//Покдлючаем языковые файлы
if (file_exists($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php')) {
	require_once ($mosConfig_absolute_path . '/language/' . $mosConfig_lang . '.php');
} elseif (file_exists($mosConfig_absolute_path . '/language/english.php')) {
	require_once ($mosConfig_absolute_path . '/language/english.php');
}
//В конце подключаем ВиртуМарт и все его конфиги
global $database;
require_once ($mosConfig_absolute_path . '/administrator/components/com_virtuemart/virtuemart.cfg.php');
include_once (ADMINPATH . '/compat.joomla1.5.php');
require_once (ADMINPATH . 'global.php');
require_once (CLASSPATH . 'ps_main.php');
require_once (CLASSPATH . 'ps_database.php');
require_once (CLASSPATH . 'ps_order.php');
require_once (CLASSPATH . 'payment/ps_interkassa2.cfg.php');

//КОНЕЦ ЭТОГО




if (count($_POST) && checkIP() && isset($_POST['ik_sign']) && isset($_POST['ik_co_id']) && isset($_POST['ik_pm_no']) && isset($_POST['ik_am'])) {

	//получаем данные заказа
	include_once(CLASSPATH . "payment/ps_interkassa2.cfg.php");

	$db = new ps_DB();
	$sql = "SELECT order_id, order_number, user_id, order_total FROM #__vm_orders WHERE order_id='{$_POST['ik_pm_no']}'";
	$db->query($sql);
	$db->next_record();
	$user = $db->f('user_id');
	$order_id = $db->f('order_id');
	$ik_am = sprintf("%.2f", $db->f('order_total'));
	$ik_co_id = INTERKASSA_CO_ID;

	wrlog($_POST);

	if ($_POST['ik_inv_st'] == 'success' && $ik_co_id == $_POST['ik_co_id'] && $ik_am == $_POST['ik_am']) {

		wrlog('rest params ok');

		if (isset($_REQUEST['ik_pw_via']) && $_REQUEST['ik_pw_via'] == 'test_interkassa_test_xts') {
			$secret_key = T_KEY;
		} else {
			$secret_key = S_KEY;
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

		wrlog($sign . '/' . $request_sign);
		//Если подписи совпадают то осуществляется смена статуса заказа в админке
		//обновление статуса заказа
		if ($request_sign == $sign) {
			wrlog('ALRIGHT!');

			$order['order_id'] = $order_id;
			$order['notify_customer'] = "Y";
			$order['order_total'] = $ik_am;
			$order['order_status'] = 'C';
			$ps_order = new ps_order();
			$ps_order->order_status_update($order);

			//отправка пользователю подтверждения об оплтае
			$subject = "Заказ № " . $order['order_id'] . " был оплачен с помощью Интеркассы.";

			$body = "Статус заказа No. " . $order['order_id'] . " был изменен.\n\n";
			$body .= "Новый статус:\n\n";
			$body .= "--------------------------------------- \n";
			$body .= "Оплачен\n";
			$body .= "--------------------------------------- \n\n";
			$body .= "Ознакомиться с деталями заказа можно по ссылке :\n";
			$body .= URL . "index.php?option=com_virtuemart&page=account.order_details&order_id=" . $order['order_id'] . "\n";

			vmMail($mosConfig_mailfrom, $mosConfig_fromname, $mosConfig_mailfrom, $subject, $body);

			die("Success payment");

		} else {
			die("Signs do not match");
		}

	}else{
		die("Params do not match");
	}

}

//Функция для ведения лога
function wrlog($content)
{
	$file = 'log.txt';
	$doc = fopen($file, 'a');

	file_put_contents($file, PHP_EOL . '====================' . date("H:i:s") . '=====================', FILE_APPEND);
	if (is_array($content)) {
		foreach ($content as $k => $v) {
			if (is_array($v)) {
				wrlog($v);
			} else {
				file_put_contents($file, PHP_EOL . $k . '=>' . $v, FILE_APPEND);
			}
		}
	} else {
		file_put_contents($file, PHP_EOL . $content, FILE_APPEND);
	}
	fclose($doc);
}
//Функция проверки айпи ответа
function checkIP()
{
	$ip_stack = array(
		'ip_begin' => '151.80.190.97',
		'ip_end' => '151.80.190.104'
	);

	if (!ip2long($_SERVER['REMOTE_ADDR']) >= ip2long($ip_stack['ip_begin']) && !ip2long($_SERVER['REMOTE_ADDR']) <= ip2long($ip_stack['ip_end'])) {
		wrlog('REQUEST IP' . $_SERVER['REMOTE_ADDR'] . 'doesnt match');
		die('Ты мошенник! Пшел вон отсюда!');
	}
	return true;
}


?>
