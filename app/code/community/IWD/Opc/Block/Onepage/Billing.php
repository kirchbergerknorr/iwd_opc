<?php

class IWD_Opc_Block_Onepage_Billing extends Mage_Checkout_Block_Onepage_Billing
{
    public function getAddress()
    {
    	if (is_null($this->_address)) {
    		if ($this->isCustomerLoggedIn()) {
    			$this->_address = $this->getQuote()->getBillingAddress();
    			if(!$this->_address->getFirstname()) {
    				$this->_address->setFirstname($this->getQuote()->getCustomer()->getFirstname());
    			}
    			if(!$this->_address->getLastname()) {
    				$this->_address->setLastname($this->getQuote()->getCustomer()->getLastname());
    			}
    		} else {
    			$bill = $this->getQuote()->getBillingAddress();
    			$bill_country = $bill->getCountryId();
    			if(!empty($bill_country))
    				$this->_address = $bill;
    			else
    				$this->_address = Mage::getModel('sales/quote_address');
    		}
    	}

    	return $this->_address;
    }
}
