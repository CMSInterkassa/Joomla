<?php
/* Создано в компании www.gateon.net
 * =================================================================
 * Модуль оплаты Интеркасса 2.0 для Joomla 3.5.x +JoomShopping 4.x 
 * ПРИМЕЧАНИЕ ПО ИСПОЛЬЗОВАНИЮ
 * =================================================================
 *  Этот файл предназначен для Joomla 3.5.x и выше
 *  www.gateon.net не гарантирует правильную работу этого расширения
 *  для более ранних версий Joomla
 * =================================================================
*/
defined('_JEXEC') or die('Restricted access');
$protocol;
if(!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off' || $_SERVER['SERVER_PORT'] == 443){
  $protocol = 'https://';
} else {
  $protocol = 'http://';
}

?>

<div class="col100">
<fieldset class="adminform">
<table class="admintable" width = "100%" >

 <tr>
   <td  class="key">
     <?php echo 'Идентификатор кассы';?>
   </td>
   <td>
     <input type = "text" class = "inputbox" name = "pm_params[wallet_id]" size="45" value = "<?php echo $params['wallet_id']?>" />
     <span class="hasTooltip" title="Номер кошелька в системе Интеркасса"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>
   </td>
 </tr>
   <tr>
   <td  class="key">
     <?php echo 'Секретный ключ';?>
   </td>
   <td>
     <input type = "password" class = "inputbox" name = "pm_params[secret_key]" size="45" value = "<?php echo $params['secret_key']?>" />
     <span class="hasTooltip" title="Секретный ключ для подписи платежа"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>
   </td>
 </tr>
 <tr>
   <td  class="key">
     <?php echo 'Тестовый ключ'?>
   </td>
   <td>
     <input type = "password" class = "inputbox" name = "pm_params[secret_test_key]" size="45" value = "<?php echo $params['secret_test_key']?>" />
     <span class="hasTooltip" title="Тестовый ключ для тестовых платежей"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>
   </td>
 </tr>
    <tr>
        <td  class="key">
            <?php echo 'Включить тестовый режим'?>
        </td>
        <td>
            <input type = "checkbox" class = "inputbox" name = "pm_params[test_mode]" size="45" <?php if (isset($params['test_mode'])){echo "checked";} ?> />
            <span class="hasTooltip" title="Используйте тестовую валюту для тестирования оплат"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>
        </td>
    </tr>

 <tr>
   <td class="key">
     <?php echo 'Статус заказа после успешной транзакции';?>
   </td>
   <td>
     <?php              
         print JHTML::_('select.genericlist', $orders->getAllOrderStatus(), 'pm_params[transaction_end_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_end_status'] );
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo 'Статус заказа в процессе транзакции';?>
   </td>
   <td>
     <?php 
         echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_pending_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_pending_status']);
     ?>
   </td>
 </tr>
 <tr>
   <td class="key">
     <?php echo 'Статус заказа при неуспешной транзакции';?>
   </td>
   <td>
     <?php 
     echo JHTML::_('select.genericlist',$orders->getAllOrderStatus(), 'pm_params[transaction_failed_status]', 'class = "inputbox" size = "1"', 'status_id', 'name', $params['transaction_failed_status']);
     ?>
   </td>
 </tr>

 <tr>
   <td  class="key">
     <?php echo 'URL успешной оплаты:'?>
   </td>
   <td>
     <input style="width:725px;" type = "text" readonly class = "inputbox" name = "pm_params[success_url]" size="45" value = "<?php echo $protocol.$_SERVER['SERVER_NAME'];?>/index.php?option=com_jshopping&controller=checkout&task=step7&act=return&js_paymentclass=pm_interkassa" />
<!--     <span class="hasTooltip" title="URL успешной оплаты"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>  -->
   </td>
 </tr>

  <tr>
   <td  class="key">
     <?php echo 'URL неуспешной оплаты:'?>
   </td>
   <td>
     <input style="width:725px;" type = "text" readonly class = "inputbox" name = "pm_params[fail_url]" size="45" value = "<?php echo $protocol.$_SERVER['SERVER_NAME'];?>/index.php?option=com_jshopping&controller=checkout&task=step7&act=cancel&js_paymentclass=pm_interkassa" />
<!--     <span class="hasTooltip" title="URL неуспешной оплаты"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>  -->
   </td>
 </tr>

  <tr>
   <td  class="key">
     <?php echo 'URL взаимодействия:'?>
   </td>
   <td>
     <input style="width:725px;" type = "text" readonly class = "inputbox" name = "pm_params[notify_url]" size="45" value = "<?php echo $protocol.$_SERVER['SERVER_NAME'];?>/index.php?option=com_jshopping&controller=checkout&task=step7&act=notify&js_paymentclass=pm_interkassa&no_lang=1" />
<!--     <span class="hasTooltip" title="URL взаимодействия"><img src="/media/system/images/tooltip.png" alt="Tooltip"></span>  -->
   </td>
 </tr>


</table>
</fieldset>   
<div class="clr"></div>

