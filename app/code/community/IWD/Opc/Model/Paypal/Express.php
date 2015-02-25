<?php
class IWD_Opc_Model_Paypal_Express extends Mage_Paypal_Model_Express{


    /**
     * Checkout redirect URL getter for onepage checkout (hardcode)
     *
     * @see Mage_Checkout_OnepageController::savePaymentAction()
     * @see Mage_Sales_Model_Quote_Payment::getCheckoutRedirectUrl()
     * @return string
     */
    public function getCheckoutRedirectUrl()
    {
    	if(Mage::getStoreConfig('opc_paypal_status')){
        	return Mage::getUrl('onepage/express/start');
    	}
    	
    	return parent::getCheckoutRedirectUrl();
    }

    
}
