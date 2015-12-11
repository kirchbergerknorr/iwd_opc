<?php

class IWD_Opc_CouponController extends Mage_Core_Controller_Front_Action{

	const XML_PATH_DEFAULT_PAYMENT = 'opc/default/payment';

	/**
	 * Retrieve shopping cart model object
	 *
	 * @return Mage_Checkout_Model_Cart
	 */
	protected function _getCart(){
		return Mage::getSingleton('checkout/cart');
	}
	
	/**
	 * Get checkout session model instance
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	protected function _getSession(){
		return Mage::getSingleton('checkout/session');
	}
	
	/**
	 * Get current active quote instance
	 *
	 * @return Mage_Sales_Model_Quote
	 */
	protected function _getQuote(){
		return $this->_getCart()->getQuote();
	}
	
	
	/**
	 * Get payments method step html
	 *
	 * @return string
	 */
	protected function _getPaymentMethodsHtml($use_method = false){
	
		/** UPDATE PAYMENT METHOD **/
		// check what method to use
		$apply_method = Mage::getStoreConfig(self::XML_PATH_DEFAULT_PAYMENT);
		if($use_method)
			$apply_method = $use_method;
		
		$_cart = $this->_getCart();
		$_quote = $_cart->getQuote();
		$_quote->getPayment()->setMethod($apply_method);
		$_quote->setTotalsCollectedFlag(false)->collectTotals();
		$_quote->save();
	
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_paymentmethod');
		$layout->generateXml();
		$layout->generateBlocks();
		
		$output = $layout->getOutput();
		return $output;
	}
	
	/**
	 * Get shipping method step html
	 *
	 * @return string
	 */
	protected function _getShippingMethodsHtml(){
		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_index');
		$layout->generateXml();
		$layout->generateBlocks();
		$shippingMethods = $layout->getBlock('checkout.onepage.shipping_method');
		$shippingMethods->setTemplate('opc/onepage/shipping_method.phtml');
		return $shippingMethods->toHtml();
	}
	
	public function couponPostAction(){
		
		$responseData = array();
		/**
		 * No reason continue with empty shopping cart
		 */
		if (!$this->_getCart()->getQuote()->getItemsCount()) {
			$this->_redirect('checkout/cart');
			return;
		}
	
		$couponCode = (string) $this->getRequest()->getParam('coupon_code');
		if ($this->getRequest()->getParam('remove') == 1) {
			$couponCode = '';
		}
		$oldCouponCode = $this->_getQuote()->getCouponCode();
	
		if (!strlen($couponCode) && !strlen($oldCouponCode)) {
			$responseData['message'] = $this->__('Coupon code is not valid.');
			$this->getResponse()->setHeader('Content-type','application/json', true);
			$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
			return;
		}
	
		$helper = Mage::helper('opc');
		
		/// get list of available payment methods before discount changes
		$methods_before = $helper->getAvailablePaymentMethods();
		///////

		/// get list of available shipping methods before discount changes
		$ship_methods_before = $helper->getShippings();
		///////

		try {
			$this->_getQuote()->getShippingAddress()->setCollectShippingRates(true);
			$this->_getQuote()->setCouponCode(strlen($couponCode) ? $couponCode : '')
				->collectTotals()
				->save();
	
			/// get list of available shipping methods after discount changes
			$ship_methods_after = $helper->getShippings();
			///////
					
			/// get list of available methods after discount changes
			$methods_after = Mage::helper('opc')->getAvailablePaymentMethods();
			///////
			
			if ($couponCode) {
				if ($couponCode == $this->_getQuote()->getCouponCode()) {
					$responseData['message'] = $this->__('Coupon code "%s" was applied.', Mage::helper('core')->htmlEscape($couponCode));
				}else {				
					$responseData['message'] = $this->__('Coupon code "%s" is not valid.', Mage::helper('core')->htmlEscape($couponCode));					
				}
			} else {
				$responseData['message'] =  $this->__('Coupon code was canceled.');
			}
			
			$layout= $this->getLayout();
			$block = $layout->createBlock('checkout/cart_coupon');
			$block->setTemplate('opc/onepage/coupon.phtml');
			$responseData['coupon'] = $block->toHtml();
			
			// check if need to reload shipping methods
			$ship_changed = Mage::helper('opc')->checkUpdatedShippingMethods($ship_methods_before, $ship_methods_after);
			if($ship_changed)
				$responseData['shipping'] = $this->_getShippingMethodsHtml();
			/////
			
			// check if need to reload payment methods
			$use_method = Mage::helper('opc')->checkUpdatedPaymentMethods($methods_before, $methods_after);
			if($use_method != -1)
				$responseData['payments'] = $this->_getPaymentMethodsHtml($use_method);
			/////
			
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		} catch (Exception $e) {
			$responseData['message'] =  $this->__('Cannot apply the coupon code.');
			Mage::logException($e);
		}
		
		
		$this->getResponse()->setHeader('Content-type','application/json', true);
		$this->getResponse()->setBody(Mage::helper('core')->jsonEncode($responseData));
		
	}
}