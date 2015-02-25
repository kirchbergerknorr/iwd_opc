<?php
class IWD_Opc_Block_Paypal_login extends Mage_Core_Block_Template{

 
    protected function _toHtml(){
        $isExtensionEnabled = Mage::getStoreConfigFlag('opc/paypallogin/status');
        if ($isExtensionEnabled) {
            return parent::_toHtml();
        }
        return '';
    }
	
	public function getPayPalButtonUrl(){
		return Mage::helper('opc/paypal')->getPayPalButtonUrl();
	}

}
