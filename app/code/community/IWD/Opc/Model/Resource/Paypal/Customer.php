<?php
class IWD_Opc_Model_Resource_Paypal_Customer extends Mage_Core_Model_Resource_Db_Abstract{
	
    /**
     * Paypal customer resource model initialization
     *
     * @return void
     */
    public function _construct(){
        $this->_init('opc/customer', 'id');
    }

    /**
     * Unlinks (native magento customer entity and paypal customer entity)
     *
     * @param $customerId
     * @return Paypalauth_Identity_Model_Mysql4_Paypal_Customer
     */
    public function unlinkAccount($customerId){
        $this->_getWriteAdapter()->delete($this->getMainTable(), "customer_id={$customerId}");
        return $this;
    }

    /**
     * Check PayPal customer existing in the database
     *
     * @param string $customerId
     * @return bool
     */
    public function isPaypalCustomerExists($field, $value){
        return (bool)$this->getPaypalCustomerDataByField($field, $value);
    }

    /**
     * Return payer_id(PayPal customer id) and customer_id
     *
     * @param $field
     * @param string $value
     * @return mixed
     */
    public function getPaypalCustomerDataByField($field, $value = ''){
        $select = $this->_getReadAdapter()->select()
            ->from($this->getMainTable(), array('customer_id', 'payer_id', 'email'))
            ->where("$field=?", $value);
        $result = $this->_getReadAdapter()->fetchRow($select);

        return $result;
    }

}