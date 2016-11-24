<?php
/**
 * @name Интеркасса 2.0
 * @description Модуль разработан в компании GateOn предназначен для CMS Joomla 1.5.26 + VirtueMart 1.1.9
 * @author www.gateon.net
 * @email www@smartbyte.pro
 * @version 1.0
 */


if (!defined('_VALID_MOS') && !defined('_JEXEC'))
    die('Direct Access to ' . basename(__FILE__) . ' is not allowed.');

class ps_interkassa2{
    var $classname = "ps_interkassa2";
    var $payment_code = "IK";

    public function show_configuration()
    {
        global $VM_LANG;
        $db = new ps_DB();
        $VM_LANG->load('interkassa2');

        include_once(CLASSPATH . "payment/ps_interkassa2.cfg.php");
        ?>

        <p style="text-align: center;font-weight: bold;"><?php echo $VM_LANG->_('INTERKASSA_TITLE') ?></p>
        <p><?php echo $VM_LANG->_('INTERKASSA_DESC') ?></p><br>
        <table>
            <tr>
                <td><strong><?php echo $VM_LANG->_('INTERKASSA_CO_ID') ?></strong></td>

                <td><input type="text" name="INTERKASSA_CO_ID" class="inputbox" value="<?php if(INTERKASSA_CO_ID != 'INTERKASSA_CO_ID') echo INTERKASSA_CO_ID ?>"/></td>
            </tr>
            <tr>
                <td><strong><?php echo $VM_LANG->_('S_KEY') ?></strong></td>
                <td><input type="text" name="S_KEY" class="inputbox" value="<?php if(S_KEY != 'S_KEY') echo S_KEY ?>"/></td>
                <td><?php echo $VM_LANG->_('S_KEY_DESC') ?></td>
            </tr>
            <tr>
                <td><strong><?php echo $VM_LANG->_('T_KEY') ?></strong></td>
                <td><input type="text" name="T_KEY" class="inputbox" value="<?php  if(T_KEY != 'T_KEY') echo T_KEY ?>"/></td>
                <td><?php echo $VM_LANG->_('T_KEY_DESC') ?></td>
            </tr>
        </table>
        <?php
    }


    public function has_configuration(){
        return true;
    }

    public function configfile_writeable()
    {
        return is_writeable(CLASSPATH . "payment/ps_interkassa2.cfg.php");
    }

    public function configfile_readable()
    {
        return is_readable(CLASSPATH . "payment/ps_interkassa2.cfg.php");
    }

    public function write_configuration(&$d)
    {
        if (isset($d['INTERKASSA_CO_ID'])) {
            $my_config_array = array(
                "INTERKASSA_CO_ID" => $d['INTERKASSA_CO_ID'],
                "S_KEY" => $d['S_KEY'],
                "T_KEY" => $d['T_KEY']
            );

            $config = "<?php\n";
            $config .= "if(!defined('_VALID_MOS') && !defined('_JEXEC')) die('Direct Access to '.basename(__FILE__).' is not allowed.'); \n\n";
            foreach ($my_config_array as $key => $value) {
                $config .= "define('$key', '$value');\n";
            }
            $config .= "?>";

            if ($fp = fopen(CLASSPATH . "payment/ps_interkassa2.cfg.php", "w")) {
                fputs($fp, $config, strlen($config));
                fclose($fp);
                return true;
            } else
                return false;
        } else {
            return false;
        }
    }

    public function process_payment($order_number, $order_total, &$d)
    {
        return true;
    }

    public function get_payment_rate($subtotal)
    {

    }
}

?>