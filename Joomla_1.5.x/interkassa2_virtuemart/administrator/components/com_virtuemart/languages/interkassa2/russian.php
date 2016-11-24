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
	'INTERKASSA_TITLE' 				=> 'Интеркасса 2.0',
	'INTERKASSA_DESC' 				=> '<br>Оплата через платёжную систему <a href="https://www.interkassa.com/">Интеркасса 2.0</a>',
	'INTERKASSA_CO_ID' 				=> 'Идентификатор кассы Интеркассы',
	'PAYANYWAY_AMOUNT' 				=> 'Сумма заказа',
	'PAYANYWAY_AMOUNT_DESC' 		=> 'Сумма к оплате',

    'S_KEY'                         => 'Секретый ключ',
    'S_KEY_DESC'                    => 'Секретый ключ безопасности находится во вкладке безопасность вашей кассы',
    'T_KEY'                         => 'Тестовый ключ',
    'T_KEY_DESC'                    => 'Тестовый ключ безопасности находится во вкладке безопасность вашей кассы',

	'PAYMENT_INTERKASSA_TITLE' 		=> 'Оплата через платёжную систему <b>Интеркасса 2.0</b>',
	'PAYMENT_INTERKASSA_ORDER' 		=> 'Заказ №',
	'PAYMENT_INTERKASSA_TO_PAY' 	=> 'Сумма заказа:',
	'PAYMENT_INTERKASSA_BUTTON' 	=> 'Оплатить заказ',
); 
$VM_LANG->initModule('interkassa2', $langvars);