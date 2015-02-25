<?php
class IWD_Opc_Block_Lipp extends  Mage_Core_Block_Template{
	
	public function getJsonConfig() {
	
		$config = array ();
		
		$scheme = Mage::app()->getRequest()->getScheme();
		if ($scheme == 'http'){
			$secure = false;
		}else{
			$secure = true;
		}
		$config['baseUrl'] = Mage::getBaseUrl('link', $secure);
		$config['paypalexpress'] = Mage::getUrl('opses/express/start',array('_secure'=>$secure));
		$config['paypalLightBoxEnabled'] = Mage::helper('opc')->getPayPalLightboxEnabled();
		return Mage::helper ( 'core' )->jsonEncode ( $config );
	}
	
	
	
	
}