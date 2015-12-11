<?php
class IWD_Opc_Block_Paypal_Shortcutuk extends IWD_Opc_Block_Paypal_Shortcut
{
    /**
     * Payment method code
     *
     * @var string
     */
    protected $_paymentMethodCode = Mage_Paypal_Model_Config::METHOD_WPP_PE_EXPRESS;

    /**
     * Start express action
     *
     * @var string
     */
    protected $_startAction = 'paypaluk/express/start';

    /**
     * Express checkout model factory name
     *
     * @var string
     */
    protected $_checkoutType = 'paypaluk/express_checkout';
}