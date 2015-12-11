<?php
class IWD_Opc_Block_Adminhtml_System_Config_Form_Field_Merchant extends Mage_Adminhtml_Block_System_Config_Form_Field
{

    protected $_config = null;

    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $value = $element->getValue();
        //if(empty($value) && Mage::getStoreConfig('payment/incontext/enable')) {
        $value = $this->_getToken();
        $element->setValue($value);
        try{
            Mage::getModel('core/config')->saveConfig('payment/incontext/merchantid', $value);
        } catch (Exception $e) {
            Mage::logException($e);
        }

        //}       

        return parent::_getElementHtml($element);
    }

    protected function _getToken(){

        $payments = Mage::getSingleton('payment/config')->getActiveMethods();

        foreach ($payments as $paymentCode=>$paymentModel) {

            $api = false;
            if ($paymentCode=='paypal_express'){
                $api = Mage::getModel('opc/paypal');
                $this->_config = Mage::getModel('paypal/config', array(Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS));
                break;
            }elseif($paymentCode=='paypaluk_express'){
                $api = Mage::getModel('opc/paypaluk');
                $this->_config = Mage::getModel('paypal/config', array(Mage_Paypal_Model_Config::METHOD_WPP_PE_EXPRESS));
                break;
            }
        }

        if ($api){
            try{
                $api->setConfigObject($this->_config);
                $api->callGetPalDetails();
                $merchantId = $api->getPal();
                return $merchantId;
            } catch (Exception $e) {
                Mage::logException($e);
            }
            return '';
        }

        /*

        */

    }

}