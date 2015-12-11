<?php
if(class_exists("Mage_Paypal_Block_Bml_Form", false)){
    class IWD_Opc_Block_Paypal_Bml_Form_Extended extends Mage_Paypal_Block_Bml_Form {}
} else {
    class IWD_Opc_Block_Paypal_Bml_Form_Extended extends Mage_Paypal_Block_Standard_Form {}
}

class IWD_Opc_Block_Paypal_Bml_Form extends IWD_Opc_Block_Paypal_Bml_Form_Extended
{
    protected function _construct()
    {
        $this->_config = Mage::getModel('paypal/config')->setMethod($this->getMethodCode());
        $mark = Mage::getConfig()->getBlockClassName('core/template');
        $mark = new $mark;
        $mark->setTemplate('opc/paypal/payment/mark.phtml')
            ->setPaymentAcceptanceMarkHref('https://www.securecheckout.billmelater.com/paycapture-content/'
                . 'fetch?hash=AU826TU8&content=/bmlweb/ppwpsiw.html')
            ->setPaymentAcceptanceMarkSrc('https://www.paypalobjects.com/webstatic/en_US/i/buttons/'
                . 'ppc-acceptance-medium.png')
            ->setPaymentWhatIs('See terms');
        $this->setTemplate('paypal/payment/redirect.phtml')
            ->setRedirectMessage(
                Mage::helper('paypal')->__('You will be redirected to the PayPal website.')
            )
            ->setMethodTitle('') // Output PayPal mark, omit title
            ->setMethodLabelAfterHtml($mark->toHtml());
    }
}
