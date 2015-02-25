<?php
class IWD_Opc_Model_Paypal_Customer extends Mage_Core_Model_Abstract{

    public function _construct(){
        parent::_construct();
        $this->_init('opc/paypal_customer');
    }

    /**
     * Log in Magento
     *
     * @return Mage_Core_Model_Abstract
     */
    public function logInMagentoCustomerAccount($customerId){
        $magentoCustomer = Mage::getModel('customer/customer')->load($customerId);
        $magentoCustomer->setConfirmation(null)->save();
        $magentoCustomer->setIsJustConfirmed(true);
        Mage::getModel('customer/session')->setCustomerAsLoggedIn($magentoCustomer);
        return $magentoCustomer;
    }

    /**
     * Unlink (native magento customer entity and paypal customer entity) from dashboard
     *
     * @return Paypalauth_Identity_Model_Paypal_Customer
     */
    public function unlinkAccount(){
        $customerId = Mage::getSingleton('customer/session')->getCustomer()->getId();
        $this->_getResource()->unlinkAccount($customerId);
        return $this;
    }

    /**
     * Check paypalauth account existing in the database.
     * Method returns true if exists, false - otherwise.
     *
     * @param $field
     * @param $value
     * @return bool
     */
    public function isPaypalCustomerExists($field, $value){
       return (bool) $this->_getResource()->isPaypalCustomerExists((string) $field, (string) $value);
    }

    /**
     * Return data array from paypalauth_customer table
     *
     * @param $field
     * @param $value
     * @return
     */
    public function getPaypalCustomerDataByField($field, $value){
        $data = $this->_getResource()->getPaypalCustomerDataByField($field, $value);
        return $data;
    }

}