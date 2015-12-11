<?php

class IWD_Opc_Model_Paypaluk extends Mage_PaypalUk_Model_Api_Nvp{

	public function callGetPalDetails()
	{
		$this->_globalMap['PAL'] = 'pal';
		$this->_importFromResponse($this->_getPalDetailsResponse, array('PAL'=>$this->getVendor()));
	}

}