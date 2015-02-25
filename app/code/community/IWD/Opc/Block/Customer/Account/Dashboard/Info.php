<?php
class IWD_Opc_Block_Customer_Account_Dashboard_Info extends Mage_Customer_Block_Account_Dashboard_Info{
	
	
    /**
     * Add paypalauth block to the customer dashboard
     *
     * @return string
     */
    protected function _toHtml(){
        $isExtensionEnabled = Mage::getStoreConfigFlag('opc/paypallogin/status');

        $html = $this->getChildHtml('paypalauth_dashboard');
        if (!$isExtensionEnabled) {
            return parent::_toHtml();
        }
        $html .= parent::_toHtml();
        return $html;
    }

    /**
     * Check if this customer account linked with PayPal account
     *
     * @return bool
     */
    public function getPaypalCustomerEmail(){
        $customerId = $this->getCustomer()->getId();
        $paypalCustomerData =  Mage::getModel('opc/paypal_customer')-> getPaypalCustomerDataByField('customer_id', $customerId);

        if ($paypalCustomerData['email']) {
            return $paypalCustomerData['email'];
        }
        return false;
    }

    /**
     * Return action url for unlinking (native magento customer entity and paypal customer entity)
     *
     * @return string
     */
    public function getUnlinkUrl(){
        return Mage::getUrl('onepage/paypal/unlink');
    }

    /**
     * Return action url for authorized magento customer
     *
     * @return string
     */
    public function getAuthLoginUrl(){
        return Mage::getUrl('onepage/paypal/login');
    }
}
