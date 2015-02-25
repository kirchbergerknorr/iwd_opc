<?php

/**
 * Config model that is aware of all Mage_Paypal payment methods
 * Works with PayPal-specific system configuration
 */

class IWD_Opc_Model_Paypal_Config extends Mage_Paypal_Model_Config{
	
	/**
	 * BN code getter
	 *
	 * @param string $countryCode ISO 3166-1
	 */
	public function getBuildNotationCode($countryCode = null){
		parent::getBuildNotationCode($countryCode);
		
		return 'IWD_SI_Other_OnePage';
	}
}