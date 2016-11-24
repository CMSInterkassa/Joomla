<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Joomla 1.5.26 + VirtueMart 1.1.9
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.0
 */
if( !defined( '_VALID_MOS' ) && !defined( '_JEXEC' ) ) die( 'Direct Access to '.basename(__FILE__).' is not allowed.' ); 

global $VM_LANG;
$langvars = array (
	'INTERKASSA_TITLE' 				=> 'Interkassa 2.0',
	'INTERKASSA_DESC' 				=> '<br>Payment via <a href="https://www.interkassa.com/">Interkassa 2.0</a>',
	'INTERKASSA_CO_ID' 				=> 'Shop id',
	'PAYANYWAY_AMOUNT' 				=> 'Order amount',
	'PAYANYWAY_AMOUNT_DESC' 		=> 'Amount of payment',

    'S_KEY'                         => 'Secret key',
    'S_KEY_DESC'                    => 'Secret security key. Can be found in security settings',
    'T_KEY'                         => 'Test key',
    'T_KEY_DESC'                    => 'Test security key. Can be found in security settings',
	
	'PAYMENT_PAYANYWAY_TITLE' 		=> 'Payment via <b>Interkassa 2.0</b> system',
	'PAYMENT_PAYANYWAY_ORDER' 		=> 'Order №',
	'PAYMENT_PAYANYWAY_TO_PAY' 		=> 'Amount of payment:',
	'PAYMENT_PAYANYWAY_BUTTON' 		=> 'Pay via Interkassa',
);
$VM_LANG->initModule('interkassa2', $langvars);