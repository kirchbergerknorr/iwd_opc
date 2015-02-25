<?php
$file = Mage::getBaseDir('code').'/local/Braintree/Payments/Model/Paymentmethod.php';
if(file_exists($file)){
	if(!class_exists('Braintree_Payments_Model_Paymentmethod', false))
		include_once($file);	
	class IWD_Opc_Model_Braintree_Paymentmethod extends Braintree_Payments_Model_Paymentmethod{
	
		/**
	 	* Format param "channel" for transaction
		*
		* @return string
		*/
		protected function _getChannel()
		{
			return 'Magento-IWD';
		}
	}
}