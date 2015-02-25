<?php
class IWD_Opc_Helper_Data extends Mage_Core_Helper_Abstract{
	
	private $_version = 'CE';
	
	const XML_PATH_VAT_FRONTEND_VISIBILITY = 'customer/create_account/vat_frontend_visibility';
	
	const XML_PATH_SHIPPING_VISIBILITY = 'opc/default/show_shipping';
	
	const XML_PATH_TERMS_TYPE = 'opc/default/terms_type';
	
	const XML_PATH_COMMENT = 'opc/default/comment';
	
	const XML_PATH_DISCOUNT = 'opc/default/discount';
	
	const XML_PAYPAL_LIGHTBOX_SANDBOX = 'opc/paypal/sandbox';
	
	const XML_PAYPAL_LIGHTBOX_ENABLED = 'opc/paypal/status';
	
	public function isAvailableVersion(){
	
		$mage  = new Mage();
		if (!is_callable(array($mage, 'getEdition'))){
			$edition = 'Community';
		}else{
			$edition = Mage::getEdition();
		}
		unset($mage);
			
		if ($edition=='Enterprise' && $this->_version=='CE'){
			return false;
		}
		return true;
	
	}
	
	public function isEnable(){
		$status = Mage::getStoreConfig('opc/global/status');		
		return $status;
	}
	
	/**
	 * Get string with frontend validation classes for attribute
	 *
	 * @param string $attributeCode
	 * @return string
	 */
	public function getAttributeValidationClass($attributeCode){
		/** @var $attribute Mage_Customer_Model_Attribute */
		$attribute = isset($this->_attributes[$attributeCode]) ? $this->_attributes[$attributeCode]
		: Mage::getSingleton('eav/config')->getAttribute('customer_address', $attributeCode);
		$class = $attribute ? $attribute->getFrontend()->getClass() : '';
	
		if (in_array($attributeCode, array('firstname', 'middlename', 'lastname', 'prefix', 'suffix', 'taxvat'))) {
			if ($class && !$attribute->getIsVisible()) {
				$class = ''; // address attribute is not visible thus its validation rules are not applied
			}
	
			/** @var $customerAttribute Mage_Customer_Model_Attribute */
			$customerAttribute = Mage::getSingleton('eav/config')->getAttribute('customer', $attributeCode);
			$class .= $customerAttribute && $customerAttribute->getIsVisible()
			? $customerAttribute->getFrontend()->getClass() : '';
			$class = implode(' ', array_unique(array_filter(explode(' ', $class))));
		}
	
		return $class;
	}
	
	public function isVatAttributeVisible(){
		return (bool)Mage::getStoreConfig(self::XML_PATH_VAT_FRONTEND_VISIBILITY);
	}
	
	
	public function isEnterprise(){
		return Mage::getConfig()->getModuleConfig('Enterprise_Enterprise') && Mage::getConfig()->getModuleConfig('Enterprise_AdminGws') && Mage::getConfig()->getModuleConfig('Enterprise_Checkout') && Mage::getConfig()->getModuleConfig('Enterprise_Customer');
	}
	
	
	public function isShowShippingForm(){
		return (bool) Mage::getStoreConfig(self::XML_PATH_SHIPPING_VISIBILITY);
	}
	
	public function getTermsType(){
		return Mage::getStoreConfig(self::XML_PATH_TERMS_TYPE);
	}
	
	public function isShowComment(){
		return Mage::getStoreConfig(self::XML_PATH_COMMENT);
	}

	public function isShowDiscount(){
		return Mage::getStoreConfig(self::XML_PATH_DISCOUNT);
	}
	
	public function getPayPalExpressUrl($token){
		
		if (Mage::getStoreConfig(self::XML_PAYPAL_LIGHTBOX_SANDBOX)){
			return 'https://www.sandbox.paypal.com/checkoutnow?token='.$token;
		}else{
			return 'https://www.paypal.com/checkoutnow?token='.$token;
		}
	
	}
	
	public function getPayPalLightboxEnabled(){
		return (bool)Mage::getStoreConfig(self::XML_PAYPAL_LIGHTBOX_ENABLED);
	}
	
	public function getAvailablePaymentMethods()
	{
		$payment_methods = array();
		$methods = Mage::app()->getLayout()->createBlock('checkout/onepage_payment_methods')->getMethods();
		foreach ($methods as $_method)
		{
			$_code = $_method->getCode();
			$payment_methods[] = $_code;
		}
	
		return $payment_methods;
	}
	
	public function getSelectedPaymentMethod()
	{
		return Mage::app()->getLayout()->createBlock('checkout/onepage_payment_methods')->getSelectedMethodCode();
	}
	
	/**
	 * check if list of available methods was changed
	 * 
	 * @param array $methods_before
	 * @param array $methods_after
	 * @return string - method to use
	 */
	public function checkUpdatedPaymentMethods($methods_before, $methods_after)
	{
		// check if need to reload payment methods
		$selected_method_code = $this->getSelectedPaymentMethod();
		if(!in_array($selected_method_code, $methods_after))
			$selected_method_code = false;
			
		$pm_changed = false;
		if(count($methods_before) != count($methods_after))
			$pm_changed = true;
		
		$free_available = false;
		foreach($methods_after as $_code)
		{
			if($_code == 'free')
				$free_available = $_code;
			if(!$pm_changed)
			{
				if(!in_array($_code, $methods_before))
					$pm_changed = true;
			}
		}
		
		if($pm_changed)
		{
			$use_method = $selected_method_code;
			if($free_available)
				$use_method = $free_available;
			return $use_method;
		}
		//////
		
		return -1; // no changes 
	}	
	
	public function getGrandTotal(){
	    $quote = Mage::getModel('checkout/session')->getQuote();
	    $total = $quote->getGrandTotal();
	     
	    return Mage::helper('checkout')->formatPrice($total);
	}
	
}