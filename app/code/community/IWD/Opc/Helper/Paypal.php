<?php
class IWD_Opc_Helper_Paypal extends Mage_Customer_Helper_Data {
	
	const RETURN_URL = 'onepage/paypal/return';
	
	const XML_PATH_ENABLED = 'opc/paypallogin/status';
	
	const XML_PATH_SANDBOX_MODE = 'opc/paypallogin/sandbox';
	
	const XML_PATH_CLIENT_ID = 'opc/paypallogin/clientid';
	
	const XML_PATH_CLIENT_SECRET = 'opc/paypallogin/secret';
	
	private $_clientId;
	
	private $_clientSecret;
	
	private $_sandbox;
	
	private $_scopes = 'openid email profile address phone https://uri.paypal.com/services/expresscheckout';
	
	private $_returnUrl;
	
	private $_paypalSandboxUrl = 'https://www.sandbox.paypal.com/';
	
	private $_paypalSandboxApiUrl = 'https://api.sandbox.paypal.com/';
	
	private $_paypalLiveUrl = 'https://www.paypal.com/';
	
	private $_paypalLiveApiUrl = 'https://api.paypal.com/';
	
	public function __construct() {
		
		$this->_sandbox = Mage::getStoreConfig ( self::XML_PATH_SANDBOX_MODE );
		$this->_clientId = Mage::getStoreConfig ( self::XML_PATH_CLIENT_ID );
		$this->_clientSecret = Mage::getStoreConfig ( self::XML_PATH_CLIENT_SECRET );
	}
	
	/**
	 * Return url with image for PayPal Access button
	 *
	 * @return string
	 */
	public function getPayPalButtonUrl() {
		return "https://www.paypalobjects.com/webstatic/en_US/developer/docs/lipp/loginwithpaypalbutton.png";
	}
	
	
	public function getAuthUrl() {
		$token = new stdClass ();
		
		$token->nonce = time () . uniqid ();
		
		$token->state = time () . uniqid ();
		
		Mage::getSingleton ( 'core/session' )->setPayPalAuthToken ( $token );
		
		$authUrl = sprintf ( "%s?client_id=%s&response_type=code&scope=%s&redirect_uri=%s&nonce=%s&state=%s", $this->getAuthorizationEndPoint (), Mage::getStoreConfig ( self::XML_PATH_CLIENT_ID ), urlencode ( $this->_scopes ), urlencode ( Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_LINK, true ) . self::RETURN_URL ), $token->nonce, $token->state );
		return $authUrl;
	}
	
	
	private function getAuthorizationEndPoint() {
		if ($this->_sandbox) {
			$url = $this->_paypalSandboxUrl;
		} else {
			$url = $this->_paypalLiveUrl;
		}
		
		return $url . 'webapps/auth/protocol/openidconnect/v1/authorize';
	}
	
	
	private function getAccessTokenEndPoint() {
		
		if ($this->_sandbox) {
			$url = $this->_paypalSandboxApiUrl;
		} else {
			$url = $this->_paypalLiveApiUrl;
		}
		
		return $url . 'v1/identity/openidconnect/tokenservice';
	}
	
	
	private function getProfileEndPoint() {
		if ($this->_sandbox) {
			$url = $this->_paypalSandboxApiUrl;
		} else {
			$url = $this->_paypalLiveApiUrl;
		}
		
		return $url . 'v1/identity/openidconnect/userinfo';
	}
	
	
	private function getLogoutEndPoint() {
		if ($this->_sandbox) {
			$url = $this->_paypalSandboxUrl;
		} else {
			$url = $this->_paypalLiveUrl;
		}
		
		return $url . 'webapps/auth/protocol/openidconnect/v1/endsession';
	}
	
	
	private function getValidateEndPoint() {
		if ($this->_sandbox) {
			$url = $this->_paypalSandboxApiUrl;
		} else {
			$url = $this->_paypalLiveApiUrl;
		}
		
		return $url . 'webapps/auth/protocol/openidconnect/v1/checkid';
	}
	
	
	public function getAccessToken($params) {
		$_code = $params ['code'];
		
		$_state = $params ['state'];
		
		$_token = Mage::getSingleton ( 'core/session' )->getPayPalAuthToken ();
		
		if ($_token->state == $_state) {
			
			$request = new Varien_Object ();
			$request->setData ( 'grant_type', 'authorization_code' );
			$request->setData ( 'code', $_code );
			$request->setData ( 'redirect_uri', Mage::getBaseUrl ( Mage_Core_Model_Store::URL_TYPE_LINK, true ) . self::RETURN_URL );
			
			$response = $this->_tokenWorker ( $_token, $request );
			
			$_token->received_time = time ();
			$_token->refresh_token = $response->getRefreshToken ();
			$_token->access_token = $response->getAccessToken ();
			$_token->expires_in = $response->getExpiresIn ();
			
			Mage::getSingleton ( 'core/session' )->setPayPalAuthToken ( $_token );
		}
		
		return $_token;
	}
	
	
	public function getPayPalProfile() {
		$_token = Mage::getSingleton ( 'core/session' )->getPayPalAuthToken ();
		
		$request = new Varien_Object ();
		$client = new Varien_Http_Client ();
		$result = new Varien_Object ();
		
		$_config = array (
				'maxredirects' => 5,
				'timeout' => 5 
		);
		
		$url = $this->getProfileEndPoint ();
		$params ['schema'] = 'openid';
		
		$requestUrl = $url . '/?' . $this->_buildQuery ( $params );
		
		$debugData = array (
				'url' => $requestUrl,
				'params' => $params,
				'access_token' => $_token->access_token 
		);
		$this->_debug ( $debugData );
		$client->setUri ( $requestUrl )->setConfig ( $_config )->setMethod ( Zend_Http_Client::GET )->setHeaders ( 'Content-Type: application/json' )->setHeaders ( 'Authorization: Bearer ' . $_token->access_token );
		
		try {
			
			$response = $client->setUrlEncodeBody ( true )->request ();
		} catch ( Exception $e ) {
			$result->setResponseCode ( - 1 )->setResponseReasonCode ( $e->getCode () )->setResponseReasonText ( $e->getMessage () );
			
			$debugData ['result'] = $result->getData ();
			$this->_debug ( $debugData );
			throw new Exception ( $e->getMessage () );
		}
		
		$body = $response->getBody ();
		
		if (empty ( $body )) {
			throw new Exception ( $this->__ ( 'Unable to communicate with the PayPal gateway.' ) );
		}
		
		$profile = Mage::helper ( 'core' )->jsonDecode ( $body );
		
		$result->addData ( $profile );
		
		$debugData ['result'] = $result->getData ();
		
		$this->_debug ( $debugData );
		
		return $result;
	}
	
	
	public function linkAccounts($_profile, $customer) {
		$customerId = $customer->getId ();
		
		if (! $customer->getAddresses ()) {
			$this->addPaypalAddress ( $customer, $_profile, true );
		}
		
		$payerId = $_profile->getUserId ();
		$email = $_profile->getEmail ();
		
		$paypalCustomer = Mage::getModel ( 'opc/paypal_customer' )->load ( $customerId, 'customer_id' );
		
		if ($paypalCustomer->getId ()) {
			return $paypalCustomer;
		}
		
		$paypalCustomer->setData ( 'payer_id', $payerId );
		$paypalCustomer->setData ( 'customer_id', $customerId );
		$paypalCustomer->setData ( 'email', $email );
		$paypalCustomer->save ();
		
		return $paypalCustomer;
	}
	
	
	public function addPaypalAddress($customer, $response, $default = false) {
		$customerId = $customer->getId ();
		
		$paypalAddress = $response->getAddress ();
		
		$regionId = Mage::getModel ( 'directory/region' )->loadByCode ( $paypalAddress ['region'], $paypalAddress ['country'] )->getId ();
		
		$address = Mage::getModel ( 'customer/address' );
		$address->setCustomerId ( $customerId );
		$address->setFirstname ( $response->getGivenName () );
		$address->setLastname ( $response->getFamilyName () );
		$address->setCountryId ( $paypalAddress ['country'] );
		$address->setCity ( $paypalAddress ['locality'] );
		
		if ($regionId) {
			$address->setRegionId ( $regionId );
		}
		$address->setPostcode ( $paypalAddress ['postal_code'] );
		$address->setStreet ( $paypalAddress ['street_address'] );
		$address->setTelephone ( $response->getPhoneNumber () );
		
		if ($default == true) {
			$address->setIsDefaultBilling ( true );
			$address->setIsDefaultShipping ( true );
		}
		
		$address->save ();
		$customer->addAddress ( $address );
		return $address;
	}
	
	

	public function createMagentoCustomer($response) {
		$customer = Mage::getModel ( 'customer/customer' )->setId ( null );
		$password = $customer->generatePassword ();
		
		$customer->setData ( 'firstname', $response->getGivenName () );
		$customer->setData ( 'lastname', $response->getFamilyName () );
		$customer->setData ( 'email', $response->getEmail () );
		$customer->setData ( 'password', $password );
		$customer->save ();
		
		$customerId = $customer->getId ();
		$this->addPaypalAddress ( $customer, $response, true );
		
		return $customer;
	}
	
	
	protected function _getCustomerSession() {
		return Mage::getSingleton ( 'customer/session' );
	}
	
	
	protected function _debug($debugData) {
		if ($this->_sandbox) {
			Mage::getModel ( 'core/log_adapter', 'paypal_login.log' )->log ( $debugData );
		}
	}
	
	
	protected function _buildQuery($request) {
		return http_build_query ( $request );
	}
	public function getLoginUrl() {
		return $this->_getUrl ( 'onepage/paypal/login', array (
				'_secure' => true 
		) );
	}
	
	
	public function getAskLinkPostActionUrl() {
		$params = array ();
		if ($this->_getRequest ()->getParam ( self::REFERER_QUERY_PARAM_NAME )) {
			$params = array (
					self::REFERER_QUERY_PARAM_NAME => $this->_getRequest ()->getParam ( self::REFERER_QUERY_PARAM_NAME ),
					'_secure' => true 
			);
		}
		return $this->_getUrl ( 'onepage/paypal/askLinkPost', $params );
	}
	
	
	public function refreshAccessToken() {
		$_token = Mage::getSingleton ( 'core/session' )->getPayPalAuthToken ();
		
		if ($this->isTokenExpired ( $_token ) && isset ( $_token->refresh_token )) {
			
			$request = new Varien_Object ();
			$request->setData ( 'grant_type', 'refresh_token' );
			$request->setData ( 'refresh_token', $_token->refresh_token );
			
			$response = $this->_tokenWorker ( $_token, $request );
			if ($response) {
				
				$_token->received_time = time ();
				$_token->access_token = $response->getAccessToken ();
				$_token->expires_in = $response->getExpiresIn ();
				Mage::getSingleton ( 'core/session' )->setPayPalAuthToken ( $_token );
			}
		}
		
		return $_token;
	}
	
	
	private function _tokenWorker($token, $request) {
		$base64ClientID = base64_encode ( $this->_clientId . ":" . $this->_clientSecret );
		
		$client = new Varien_Http_Client ();
		$result = new Varien_Object ();
		
		$_config = array (
				'maxredirects' => 5,
				'timeout' => 5 
		);
		
		$client->setUri ( $this->getAccessTokenEndPoint () )->setConfig ( $_config )->setMethod ( Zend_Http_Client::POST )->setParameterPost ( $request->getData () )->setHeaders ( 'User-Agent: Varien_Http_Client' )->setHeaders ( 'Authorization: Basic ' . $base64ClientID )->setHeaders ( 'Accept: */*' );
		
		try {
			
			$response = $client->setUrlEncodeBody ( false )->request ();
		} catch ( Exception $e ) {
			
			$result->setResponseCode ( - 1 )->setResponseReasonCode ( $e->getCode () )->setResponseReasonText ( $e->getMessage () );
			
			$debugData ['result'] = $result->getData ();
			$this->_debug ( $debugData );
			throw new Mage_Exception ( $e->getMessage () );
			return false;
		}
		
		$body = Mage::helper ( 'core' )->jsonDecode ( $response->getBody () );
		
		$result->addData ( $body );
		
		$debugData ['result'] = $result->getData ();
		
		$this->_debug ( $debugData );
		
		if (isset ( $debugData ['result'] ['error'] )) {
			Mage::log ( $debugData ['result'] ['error_description'] );
			
			return false;
		}
		
		return $result;
	}
	
	
	private function isTokenExpired($token) {
		$token_expired = true;
		if (! isset ( $token->expires_in )) {
			return $token_expired;
		}
		$expires_in = $token->expires_in;
		$received_time = $token->received_time;
		if (isset ( $expires_in ) && isset ( $received_time )) {
			$now = time ();
			if (($received_time + $expires_in) > $now) {
				$token_expired = false;
			}
		}
		return $token_expired;
	}
	
	
	public function end_session() {
		$token = Mage::getSingleton ( 'core/session' )->getPayPalAuthToken ();
		$logout_url = sprintf ( "%s?id_token=%s&state=%s&redirect_uri=%s", $this->getLogoutEndPoint (), $token->id_token, $token->state, $this->returnUrl . "&logout=true" );
		
		$this->run_curl ( $logout_url );
	}
	
	
	
	
	public function removeToken(){
		Mage::getSingleton('core/session')->unsPayPalExpressToKen();
	}
	
}