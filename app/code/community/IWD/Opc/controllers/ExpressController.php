<?php
class IWD_Opc_ExpressController extends  Mage_Core_Controller_Front_Action{
	

	/**
	 * @var Mage_Paypal_Model_Express_Checkout
	 */
	protected $_checkout = null;
	
	/**
	 * @var Mage_Paypal_Model_Config
	 */
	protected $_config = null;
	
	/**
	 * @var Mage_Sales_Model_Quote
	 */
	protected $_quote = false;
	
	/**
	 * Config mode type
	 *
	 * @var string
	 */
	protected $_configType = 'paypal/config';
	
	/**
	 * Config method type
	 *
	 * @var string
	 */
	protected $_configMethod = Mage_Paypal_Model_Config::METHOD_WPP_EXPRESS;
	
	/**
	 * Checkout mode type
	 *
	 * @var string
	 */
	protected $_checkoutType = 'paypal/express_checkout';
	
	/**
	 * Instantiate config
	 */
	protected function _construct(){
		parent::_construct();
		$this->_config = Mage::getModel($this->_configType, array($this->_configMethod));
	}
	
	/**
	 * Start Express Checkout by requesting initial token and dispatching customer to PayPal
	 */
	public function startAction(){
		$responseData = array();
		try {
			
			$_scheme = Mage::app()->getRequest()->getScheme();
			if ($_scheme=='https'){
				$_secure = true;
			}else{
				$_secure = false;
			}
			
			$this->_initCheckout();
	
			if ($this->_getQuote()->getIsMultiShipping()) {
				$this->_getQuote()->setIsMultiShipping(false);
				$this->_getQuote()->removeAllAddresses();
			}
	
			$customer = Mage::getSingleton('customer/session')->getCustomer();
			$quoteCheckoutMethod = $this->_getQuote()->getCheckoutMethod();
			if ($customer && $customer->getId()) {
				$this->_checkout->setCustomerWithAddressChange(
						$customer, $this->_getQuote()->getBillingAddress(), $this->_getQuote()->getShippingAddress()
				);
			} elseif ((!$quoteCheckoutMethod
				|| $quoteCheckoutMethod != Mage_Checkout_Model_Type_Onepage::METHOD_REGISTER)
				&& !Mage::helper('checkout')->isAllowedGuestCheckout(
					$this->_getQuote(),
					$this->_getQuote()->getStoreId()
			)) {
				Mage::getSingleton('core/session')->addNotice(
					Mage::helper('paypal')->__('To proceed to Checkout, please log in using your email address.')
				);
				$this->redirectLogin();
				Mage::getSingleton('customer/session')
					->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
				return;
			}
	
			// billing agreement
			$isBARequested = (bool)$this->getRequest()->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
			if ($customer && $customer->getId()) {
				$this->_checkout->setIsBillingAgreementRequested($isBARequested);
			}
	
			// Bill Me Later
			$this->_checkout->setIsBml((bool)$this->getRequest()->getParam('bml'));
				
			// giropay
			$this->_checkout->prepareGiropayUrls(
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $_secure) . 'checkout/onepage/success',
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $_secure). 'onepage/express/cancel',
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $_secure) . 'checkout/onepage/success'
			);
	
			// fix for newer magentos
			$new_mage = $this->isNewMagento();
			if($new_mage){
				$token = $this->_checkout->start(
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $_secure) . 'onepage/express/return',
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $_secure) . 'onepage/express/cancel',
					1
				);
			}
			else{
				$token = $this->_checkout->start(
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $_secure) . 'onepage/express/return',
					Mage::getBaseUrl(Mage_Core_Model_Store::URL_TYPE_LINK, $_secure) . 'onepage/express/cancel'
				);
			}
			//
			
			$this->_initToken($token);
			
			$paypalUrl =  Mage::helper('opc')->getPayPalExpressUrl($token);
			
			$this->_redirectUrl($paypalUrl);
		
		} catch (Mage_Core_Exception $e) {
			$this->_getSession()->addError($e->getMessage());
		} catch (Exception $e) {
			$this->_getSession()->addError($this->__('Unable to start Express Checkout.'));
			Mage::logException($e);
		}
	
		
	}
	
	
	/**
	 * Cancel Express Checkout
	 */
	public function cancelAction(){
		try {
			$this->_initToken(false);
			// TODO verify if this logic of order cancelation is deprecated
			// if there is an order - cancel it
			$orderId = $this->_getCheckoutSession()->getLastOrderId();
			$order = ($orderId) ? Mage::getModel('sales/order')->load($orderId) : false;
			if ($order && $order->getId() && $order->getQuoteId() == $this->_getCheckoutSession()->getQuoteId()) {
				$order->cancel()->save();
				$this->_getCheckoutSession()
				->unsLastQuoteId()
				->unsLastSuccessQuoteId()
				->unsLastOrderId()
				->unsLastRealOrderId()
				->addSuccess($this->__('Express Checkout and Order have been canceled.'))
				;
			} else {
				$this->_getCheckoutSession()->addSuccess($this->__('Express Checkout has been canceled.'));
			}
		} catch (Mage_Core_Exception $e) {
			$this->_getCheckoutSession()->addError($e->getMessage());
		} catch (Exception $e) {
			$this->_getCheckoutSession()->addError($this->__('Unable to cancel Express Checkout.'));
			Mage::logException($e);
		}
	
		
		$this->_redirect('checkout/cart', array('_secure'=>true));
		
	}
	
	/**
	 * Return from PayPal and dispatch customer to order review page
	 */
	public function returnAction(){
		$new_mage = $this->isNewMagento();
		if($new_mage){ // for newer magentos
			if ($this->getRequest()->getParam('retry_authorization') == 'true'
				&& is_array($this->_getCheckoutSession()->getPaypalTransactionData())
			) {
				$this->_forward('placeOrder');
				return;
			}
			
			try {
				$this->_getCheckoutSession()->unsPaypalTransactionData();
				$this->_checkout = $this->_initCheckout();
				$this->_checkout->returnFromPaypal($this->_initToken());

				if ($this->_checkout->canSkipOrderReviewStep()) {
					$this->_forward('placeOrder');
				} else {
					Mage::getSingleton ( 'core/session' )->unsPplRedirect ( );
					$this->_redirect('paypal/express/review', array('_secure'=>true));
				}
				return;
			} catch (Mage_Core_Exception $e) {
				Mage::getSingleton('checkout/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
				Mage::logException($e);
			}
			$this->_redirect('checkout/cart');
		}
		else{ // for older magentos
			try {
				$this->_initCheckout();
				$this->_checkout->returnFromPaypal($this->_initToken());
				Mage::getSingleton ( 'core/session' )->unsPplRedirect ( );
				echo "<script>parent.parent.location.href='" . Mage::getUrl('paypal/express/review', array('_secure'=>true)) . "'</script>";
				return;
			}
			catch (Mage_Core_Exception $e) {
				Mage::getSingleton('checkout/session')->addError($e->getMessage());
			}
			catch (Exception $e) {
				Mage::getSingleton('checkout/session')->addError($this->__('Unable to process Express Checkout approval.'));
				Mage::logException($e);
			}
			$this->_redirect('checkout/cart', array('_secure'=>true));
		}	
	}
	
	/**
	 * Instantiate quote and checkout
	 * @throws Mage_Core_Exception
	 */
	private function _initCheckout(){
		$quote = $this->_getQuote();
		if (!$quote->hasItems() || $quote->getHasError()) {
			$this->getResponse()->setHeader('HTTP/1.1','403 Forbidden');
			Mage::throwException(Mage::helper('paypal')->__('Unable to initialize Express Checkout.'));
		}
		$this->_checkout = Mage::getSingleton($this->_checkoutType, array(
				'config' => $this->_config,
				'quote'  => $quote,
		));
		return $this->_checkout;
	}
	
	/**
	 * Search for proper checkout token in request or session or (un)set specified one
	 * Combined getter/setter
	 *
	 * @param string $setToken
	 * @return Mage_Paypal_ExpressController|string
	 */
	protected function _initToken($setToken = null){
		if (null !== $setToken) {
			if (false === $setToken) {
				// security measure for avoid unsetting token twice
				if (!$this->_getSession()->getExpressCheckoutToken()) {
					Mage::throwException($this->__('PayPal Express Checkout Token does not exist.'));
				}
				$this->_getSession()->unsExpressCheckoutToken();
			} else {
				$this->_getSession()->setExpressCheckoutToken($setToken);
			}
			return $this;
		}
		if ($setToken = $this->getRequest()->getParam('token')) {
			if ($setToken !== $this->_getSession()->getExpressCheckoutToken()) {
				Mage::throwException($this->__('Wrong PayPal Express Checkout Token specified.'));
			}
		} else {
			$setToken = $this->_getSession()->getExpressCheckoutToken();
		}
		return $setToken;
	}
	
	/**
	 * PayPal session instance getter
	 *
	 * @return Mage_PayPal_Model_Session
	 */
	private function _getSession(){
		return Mage::getSingleton('paypal/session');
	}
	
	/**
	 * Return checkout session object
	 *
	 * @return Mage_Checkout_Model_Session
	 */
	private function _getCheckoutSession(){
		return Mage::getSingleton('checkout/session');
	}
	
	/**
	 * Return checkout quote object
	 *
	 * @return Mage_Sale_Model_Quote
	 */
	private function _getQuote(){
		if (!$this->_quote) {
			$this->_quote = $this->_getCheckoutSession()->getQuote();
		}
		return $this->_quote;
	}
	
	/**
	 * Redirect to login page
	 *
	 */
	public function redirectLogin()
	{
		$this->setFlag('', 'no-dispatch', true);
		$this->getResponse()->setRedirect(
			Mage::helper('core/url')->addRequestParam(
				Mage::helper('customer')->getLoginUrl(),
				array('context' => 'checkout')
			)
		);
	}

	public function isNewMagento(){
		$mage  = new Mage();
		if (!is_callable(array($mage, 'getEdition'))){
			$edition = 'Community';
		}else{
			$edition = Mage::getEdition();
		}
		unset($mage);
		
		$version = Mage::getVersionInfo();
		$m1 = $version['major'];
		$m2 = $version['minor'];
		$v = $m1*1000+$m2*10;

		if($edition == 'Enterprise'){
			if($v >= 1140) // 1.14
				return true;
		}
		else
		{
			if($v >= 1090) // 1.9
				return true;
		}
		
		return false;
	}
}