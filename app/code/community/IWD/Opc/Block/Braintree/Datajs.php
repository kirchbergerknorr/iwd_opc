<?php
$file = Mage::getBaseDir('code').'/local/Braintree/Payments/Block/Datajs.php';
if(file_exists($file)){
	if(!class_exists('Braintree_Payments_Block_Datajs', false))
		include_once($file);
	class IWD_Opc_Block_Braintree_Datajs extends Braintree_Payments_Block_Datajs{
	}
}
else{
	class IWD_Opc_Block_Braintree_Datajs extends Mage_Core_Block_Template{
	}
}