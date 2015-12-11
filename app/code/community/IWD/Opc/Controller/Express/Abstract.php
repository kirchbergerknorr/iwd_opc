<?php

abstract class IWD_Opc_Controller_Express_Abstract extends Mage_Core_Controller_Front_Action
{
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
     * Instantiate config
     */
    protected function _construct()
    {
        parent::_construct();
        $this->_config = Mage::getModel($this->_configType, array($this->_configMethod));
    }

    public function startAction()
    {

        $isNewMage = $this->isNewMagento();
        if (!$isNewMage){

            try {
                $this->_initCheckout();

                if ($this->_getQuote()->getIsMultiShipping()) {
                    $this->_getQuote()->setIsMultiShipping(false);
                    $this->_getQuote()->removeAllAddresses();
                }

                $customer = Mage::getSingleton('customer/session')->getCustomer();
                if ($customer && $customer->getId()) {
                    $this->_checkout->setCustomerWithAddressChange(
                        $customer, null, $this->_getQuote()->getShippingAddress()
                    );
                }

                // billing agreement
                $isBARequested = (bool)$this->getRequest()
                    ->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
                if ($customer && $customer->getId()) {
                    $this->_checkout->setIsBillingAgreementRequested($isBARequested);
                }

                // giropay
                $this->_checkout->prepareGiropayUrls(
                    Mage::getUrl('checkout/onepage/success'),
                    Mage::getUrl('paypal/express/cancel'),
                    Mage::getUrl('checkout/onepage/success')
                );
                $type = $this->_configMethod;$type= str_replace('_', '/', $type);
                $token = $this->_checkout->start(Mage::getUrl($type . '/return'), Mage::getUrl($type . '/cancel'));
                if ($token && $url = $this->_checkout->getRedirectUrl()) {
                    $this->_initToken($token);
                    echo $token;
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getCheckoutSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getCheckoutSession()->addError($this->__('Unable to start Express Checkout.'));
                Mage::logException($e);
            }
            echo Mage::getUrl('checkout/cart');
        }else{
            try {
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

                    Mage::getSingleton('customer/session')
                        ->setBeforeAuthUrl(Mage::getUrl('*/*/*', array('_current' => true)));
                    $this->setFlag('', 'no-dispatch', true);
                    echo Mage::getUrl('onepage');
                    return;
                }

                // billing agreement
                $isBARequested = (bool)$this->getRequest()
                    ->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_TRANSPORT_BILLING_AGREEMENT);
                if ($customer && $customer->getId()) {
                    $this->_checkout->setIsBillingAgreementRequested($isBARequested);
                }

                // Bill Me Later
                $this->_checkout->setIsBml((bool)$this->getRequest()->getParam('bml'));

                // giropay
                $this->_checkout->prepareGiropayUrls(
                    Mage::getUrl('checkout/onepage/success'),
                    Mage::getUrl('paypal/express/cancel'),
                    Mage::getUrl('checkout/onepage/success')
                );

                //$button = (bool)$this->getRequest()->getParam(Mage_Paypal_Model_Express_Checkout::PAYMENT_INFO_BUTTON);
                $type = $this->_configMethod;$type= str_replace('_', '/', $type);
                $token = $this->_checkout->start(Mage::getUrl($type . '/return'), Mage::getUrl($type . '/cancel'), true);
                if ($token && $url = $this->_checkout->getRedirectUrl()) {
                    $this->_initToken($token);
                    echo $token;
                    return;
                }
            } catch (Mage_Core_Exception $e) {
                $this->_getCheckoutSession()->addError($e->getMessage());
            } catch (Exception $e) {
                $this->_getCheckoutSession()->addError($this->__('Unable to start Express Checkout.'));
                Mage::logException($e);
            }

            echo Mage::getUrl('checkout/cart');
        }

    }

    /**
     * Instantiate quote and checkout
     * @throws Mage_Core_Exception
     */
    protected function _initCheckout()
    {
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
    protected function _initToken($setToken = null)
    {
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
    private function _getSession()
    {
        return Mage::getSingleton('paypal/session');
    }

    /**
     * Return checkout session object
     *
     * @return Mage_Checkout_Model_Session
     */
    private function _getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Return checkout quote object
     *
     * @return Mage_Sale_Model_Quote
     */
    private function _getQuote()
    {
        if (!$this->_quote) {
            $this->_quote = $this->_getCheckoutSession()->getQuote();
        }
        return $this->_quote;
    }

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

    protected function isNewMagento(){
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