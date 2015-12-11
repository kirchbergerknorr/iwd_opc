<?php

class IWD_Opc_Model_Paypal extends Mage_Paypal_Model_Api_Nvp{


    public function callGetPalDetails()
    {
        $response = $this->call('getPalDetails', array());
        $this->_importFromResponse($this->_getPalDetailsResponse, $response);
    }

}