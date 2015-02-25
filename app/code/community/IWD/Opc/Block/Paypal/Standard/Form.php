<?php

class IWD_Opc_Block_Paypal_Standard_Form extends Mage_Paypal_Block_Standard_Form{
   
    
    protected function _construct(){
                
        $this->_config = Mage::getModel('paypal/config')->setMethod($this->getMethodCode());        
        $locale = Mage::app()->getLocale();
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('paypal/payment/mark.phtml')
            ->setPaymentAcceptanceMarkHref($this->_config->getPaymentMarkWhatIsPaypalUrl($locale))
            ->setPaymentAcceptanceMarkSrc($this->_config->getPaymentMarkImageUrl($locale->getLocaleCode()))
        ; // known issue: code above will render only static mark image
        
        
       
        
        $this->setTemplate('paypal/payment/redirect.phtml')
            ->setRedirectMessage(
                Mage::helper('paypal')->__('You will be redirected to the PayPal website when you place an order.')
            )
            ->setMethodTitle(Mage::helper('paypal')->__('PayPal')) // Output PayPal mark, omit title
            ->setMethodLabelAfterHtml($mark->toHtml())
        ;
        //return parent::_construct();
    }
}
