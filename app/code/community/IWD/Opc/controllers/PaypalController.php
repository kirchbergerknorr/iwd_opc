<?php
class IWD_Opc_PaypalController extends Mage_Core_Controller_Front_Action{
	
	protected function _getSession() {
		return Mage::getSingleton ( 'customer/session' );
	}
	
	protected  function _getHelper() {
		return Mage::helper ( 'opc/paypal' );
	}
	
	/** SHOW LOGIN FORM - REDIRECT FROM MAGENTO STORE TO PAYPAL LOGIN URL**/
	public function loginAction() {
	
		$_helper = $this->_getHelper ();
		$this->_redirectUrl($_helper->getAuthUrl ());
	}
	
	public function returnAction() {
	
		$_helper = $this->_getHelper ();
		$_params = $this->getRequest()->getParams();

		try{
				
			if (isset($_params['code'])){
	
				$_helper->getAccessToken ($_params);
				$profile = $_helper->getPayPalProfile ();
	
			}else{
	
				if (isset ( $_params ['error_uri'] )) {
					$this->_getSession ()->addError ( $_params['error_description']);
					$this->_closePopup ( $this->_getloginPostRedirect () );
					return;;
				} else {
						
					$url = $_helper->getAuthUrl ();
					$this->_redirectUrl($url);
					return;;
				}
			}
				
		}catch(Exception $e){
			$this->_getSession ()->addError ( $this->__($e->getMessage()) );
			$this->_closePopup ( $this->_getloginPostRedirect () );
			return;;
		}
	
	
		$paypalCustomerData = Mage::getModel ( 'opc/paypal_customer' )->getPaypalCustomerDataByField ( 'payer_id', $profile->getUserId() );
	
		if (! $paypalCustomerData) {
			// No Link exists create the link
			if ($this->_getSession ()->isLoggedIn ()) {
				$customer = $this->_getSession ()->getCustomer ();
			} else {
				$this->_getSession ()->setData ( 'paypalData', $profile );
				$this->_getSession ()->setUsername ( $profile->getEmail() );
				$url = Mage::getUrl ( 'onepage/paypal/askLink' );
				$this->_closePopup ( $url );
				return;
			}
				
			$paypalCustomer = $this->_getHelper ()->linkAccounts ( $profile, $customer );
			$customer = $paypalCustomer->logInMagentoCustomerAccount ( $customer->getId () );
			Mage::getSingleton('customer/session')->addSuccess ( $this->__ ( "Your %s account has been linked with your PayPal login.<br /><br />You account details are displayed below. No financial information will ever be shared by PayPal.<br /><br />Simply use Login with PayPal to login when you visit.", Mage::app ()->getStore ()->getFrontendName () ) );
				
		} else {
				
			// link exists so login to store
			$paypalCustomer = Mage::getModel ( 'opc/paypal_customer' );
			$customer = $paypalCustomer->logInMagentoCustomerAccount ( $paypalCustomerData ['customer_id'] );
		}
	
		$url = $this->_getloginPostRedirect ();
		$this->_closePopup ( $url );
	
	
	}
	
	/**
	 * Define and return target URL and redirect customer after logging in
	 *
	 * @return
	 *
	 */
	protected function _getloginPostRedirect() {
		$session = $this->_getSession ();
		if (! $session->getBeforeAuthUrl () || $session->getBeforeAuthUrl () == Mage::getBaseUrl ()) {
			// Set default URL to redirect customer to
			$session->setBeforeAuthUrl ( Mage::helper ( 'customer' )->getAccountUrl () );
			// Redirect customer to the last page visited after logging in
			if ($session->isLoggedIn ()) {
				if (! Mage::getStoreConfigFlag ( 'customer/startup/redirect_dashboard' )) {
					$referer = $this->getRequest ()->getParam ( Mage_Customer_Helper_Data::REFERER_QUERY_PARAM_NAME );
					if ($referer) {
						$referer = Mage::helper ( 'core' )->urlDecode ( $referer );
						if ($this->_isUrlInternal ( $referer )) {
							$session->setBeforeAuthUrl ( $referer );
						}
					}
				} else if ($session->getAfterAuthUrl ()) {
					$session->setBeforeAuthUrl ( $session->getAfterAuthUrl ( true ) );
				}
			} else {
				$session->setBeforeAuthUrl ( Mage::helper ( 'customer' )->getLoginUrl () );
			}
		} else if ($session->getBeforeAuthUrl () == Mage::helper ( 'customer' )->getLogoutUrl ()) {
			$session->setBeforeAuthUrl ( Mage::helper ( 'customer' )->getDashboardUrl () );
		} else {
			$afterAuthUrl = $session->getAfterAuthUrl ();
			if (! $session->getAfterAuthUrl () || empty ( $afterAuthUrl )) {
				$session->setAfterAuthUrl ( $session->getBeforeAuthUrl () );
			}
			if ($session->isLoggedIn ()) {
				$session->setBeforeAuthUrl ( $session->getAfterAuthUrl ( true ) );
			}
		}
		return $session->getBeforeAuthUrl ( true );
	}
	
	
	protected function _getLoginPostUrl() {
		$session = $this->_getSession ();
	
		if ($this->getRequest ()->isPost ()) {
			$login = $this->getRequest ()->getPost ( 'login' );
			if (! empty ( $login ['username'] ) && ! empty ( $login ['password'] )) {
				try {
					$session->login ( $login ['username'], $login ['password'] );
					if ($session->getCustomer ()->getIsJustConfirmed ()) {
						$this->_welcomeCustomer ( $session->getCustomer (), true );
					}
				} catch ( Mage_Core_Exception $e ) {
					switch ($e->getCode ()) {
						case Mage_Customer_Model_Customer::EXCEPTION_EMAIL_NOT_CONFIRMED :
							$value = Mage::helper ( 'customer' )->getEmailConfirmationUrl ( $login ['username'] );
							$message = Mage::helper ( 'customer' )->__ ( 'This account is not confirmed. <a href="%s">Click here</a> to resend confirmation email.', $value );
							break;
						case Mage_Customer_Model_Customer::EXCEPTION_INVALID_EMAIL_OR_PASSWORD :
							$message = $e->getMessage ();
							$redirectUrl = 'onepage/paypal/askLink';
							break;
						default :
							$message = $e->getMessage ();
					}
					$session->addError ( $message );
					$session->setUsername ( $login ['username'] );
				} catch ( Exception $e ) {
					// Mage::logException($e); // PA DSS violation: this exception log can disclose customer password
				}
			} else {
				$session->addError ( $this->__ ( 'Login and password are required.' ) );
			}
		}
		if (empty ( $redirectUrl )) {
			$redirectUrl = $this->_getloginPostRedirect ();
		}
		return $redirectUrl;
	}
	
	
	public function unlinkAction() {
		if ($this->_getSession ()->getCustomer ()->getId ()) {
			$paypalCustomer = Mage::getModel ( 'opc/paypal_customer' )->unlinkAccount ();
			$this->_getSession ()->addSuccess ( $this->_getHelper ()->__ ( "Your %s account is no longer linked with your PayPal login.", Mage::app ()->getStore ()->getFrontendName () ) );
		}
	
		$this->_redirect ( 'customer/account' );
		return;
	}
	
	
	public function askLinkAction() {
		$paypalData = $this->_getSession ()->getData ( 'paypalData' );
		if (! $paypalData) {
			$this->_redirect ( 'customer/account' );
			return;
		}
	
		$msg = $this->_getSession ()->getMessages ( true );
		$this->loadLayout ();
		$this->getLayout ()->getMessagesBlock ()->addMessages ( $msg );
		$this->_initLayoutMessages ( 'core/session' );
		$this->renderLayout ();
	}
	
	
	public function askLinkPostAction() {
	
		$params = $this->getRequest ()->getParams ();
		$paypalData = $this->_getSession ()->getData ( 'paypalData' );
	
		if (! $paypalData) {
			$this->_redirect ( 'customer/account' );
			return;
		}
	
		$askLinkUrl = 'onepage/paypal/askLink';
	
		if (array_key_exists ( 'send', $params )) {
			$login = $this->getRequest ()->getPost ( 'login' );
			$email = $login ['username'];
			$customerId = $this->_getCustomerIdByEmail ( $email );
	
			$isPaypalCustomerExists = Mage::getModel ( 'opc/paypal_customer' )->isPaypalCustomerExists ( 'customer_id', $customerId );
	
			if ($isPaypalCustomerExists) {
				$this->_getSession ()->addError ( $this->_getHelper ()->__ ( "Your %s account is already linked with your PayPal login.", Mage::app ()->getStore ()->getFrontendName () ) );
				$this->_redirect ( $askLinkUrl );
				return;
			}
	
			$url = $this->_getLoginPostUrl ();
			if ($url == 'onepage/paypal/askLink') {
				$this->_redirect ( $url );
				return;
			}
				
			$customer = $this->_getSession ()->getCustomer ();
	
			$this->_getHelper ()->linkAccounts ( $paypalData, $customer );
			Mage::getSingleton('customer/session')->addSuccess ( $this->__ ( "Your %s account has been linked with your PayPal login<br /><br />You account details are displayed below. No financial information will ever be shared by PayPal.<br /><br />Simply use Login with PayPal to login when you visit.", Mage::app ()->getStore ()->getFrontendName () ) );
		} else {
			$customer = Mage::getModel ( 'customer/customer' );
			$paypalEmail = $paypalData->getEmail();
	
			$customerId = $this->_getCustomerIdByEmail ( $paypalEmail );
	
			if ($customerId) {
				$this->_getSession ()->setUsername ( $paypalEmail );
				$this->_getSession ()->addError ( $this->_getHelper ()->__ ( "An account already exists with that email address. Please enter the %s credentials to link the account.", Mage::app ()->getStore ()->getFrontendName () ) );
				$this->_redirect ( $askLinkUrl );
				return;
			}
			$url = $this->_getLoginPostRedirect ();
	
			try {
				$customer = $this->_getHelper ()->createMagentoCustomer ( $paypalData );
				$paypalCustomer = $this->_getHelper ()->linkAccounts ( $paypalData, $customer );
				$customer = $paypalCustomer->logInMagentoCustomerAccount ( $customer->getId () );
				Mage::getSingleton('customer/session')->addSuccess (
				$this->__ (
				"Your account has been created.<br /><br />You account details are displayed below. No financial information will ever be shared by PayPal.<br /><br />Simply use Login with PayPal to login when you visit.",
				Mage::app ()->getStore ()->getFrontendName () )
				);
			} catch ( Exception $e ) {
				$this->_getSession ()->addError ( $e->getMessage () );
			}
		}
		$this->_redirectUrl ( $url );
		return;
	}
	
	
	protected function _getCustomerIdByEmail($email) {
		$customer = Mage::getModel ( 'customer/customer' );
		$customer->setWebsiteId ( Mage::app ()->getStore ()->getWebsiteId () )->loadByEmail ( $email );
		if ($customer->getId ()) {
			return $customer->getId ();
		}
		return false;
	}
	
	
	protected function _closePopUp($url = null) {
		echo '<script type="text/javascript">window.opener.location.href="' . $url . '";self.close();</script>';
	}
}