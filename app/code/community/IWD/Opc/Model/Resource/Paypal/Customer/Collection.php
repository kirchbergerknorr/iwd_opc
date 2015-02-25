<?php
class IWD_Opc_Model_Resource_Paypal_Customer_Collection extends Mage_Core_Model_Resource_Db_Collection_Abstract{
    /**
     * Resource initialization.
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('opc/paypal_customer');
    }
}
