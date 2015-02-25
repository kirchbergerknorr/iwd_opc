<?php
class IWD_Opc_Block_Onepage_Subscribed extends Mage_Core_Block_Template{
	
	const XML_PATH_NEWSLETTER = 'opc/default/subscribe';
	const XML_PATH_NEWSLETTER_DEFAULT = 'opc/default/subscribe_default';
	
	public function getCheckByDefault(){
		return (bool) Mage::getStoreConfig(self::XML_PATH_NEWSLETTER_DEFAULT);
	}
	
	
	public function isNewsletterEnabled(){
		$enable = Mage::helper('core')->isModuleOutputEnabled('Mage_Newsletter');
		$show = (bool) Mage::getStoreConfig(self::XML_PATH_NEWSLETTER);
		if ($enable && $show && !Mage::getSingleton('customer/session')->isLoggedIn()){
			return true;
		}
		
		return false;
	}
}