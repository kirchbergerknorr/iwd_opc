<?php

class IWD_Opc_Block_Onepage_Shipping extends Mage_Checkout_Block_Onepage_Shipping
{
	public function getAddress()
	{
		if (is_null($this->_address)) {
			if ($this->isCustomerLoggedIn()) {
				$this->_address = $this->getQuote()->getShippingAddress();
			} else {
				$ship = $this->getQuote()->getShippingAddress();
				$ship_country = $ship->getCountryId();
				if(!empty($ship_country))
					$this->_address = $ship;
				else
					$this->_address = Mage::getModel('sales/quote_address');
			}
		}
	
		return $this->_address;
	}
    
}
