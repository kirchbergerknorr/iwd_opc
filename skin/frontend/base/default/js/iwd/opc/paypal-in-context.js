;
//define jquery
if(typeof($j_opc) == 'undefined' || $j_opc == undefined || !$j_opc){
    $j_opc = false;

    if(typeof($ji) != 'undefined' && $ji != undefined && $ji)
        $j_opc = $ji; // from iwd_all 2.x
    else{
        if(typeof(jQuery) != 'undefined' && jQuery != undefined && jQuery)
            $j_opc = jQuery;
    }
};

//PAYPAL LOGIC
window.paypalCheckoutReady = function() {

    if (typeof(PayPalLightboxConfig)=="undefined"){
        return;
    }

    if (typeof(PayPalLightboxConfig)!="undefined"){
        PayPalLightboxConfig = JSON.parse(PayPalLightboxConfig);
        if (PayPalLightboxConfig.isActive==0){
            return;
        }
    }


    if (PayPalLightboxConfig.isActive==0){
        return;
    }

    if (typeof(IDS)=="undefined"){
        return;
    }


    paypal.checkout.setup(PayPalLightboxConfig.merchantid, {
        environment: PayPalLightboxConfig.environment,
        button: IDS,
        click: function (e) {
            e.preventDefault();
            if ($('paypal-save-button')!=undefined){
                if (payment.currentMethod!='paypal_express' && payment.currentMethod!='paypaluk_express'){
                    return;
                }
            }
            //return if click by button in add to cart form
            if ($ji(e.target).closest('.add-to-cart').length>0){
                return;
            }


            var link = $ji(e.target).parent().attr('href');
            //click by a tag with link to express start
            if (typeof(link)!="undefined"){
                //check type of paypal express
                if (link.indexOf('paypaluk')>=0 || link.indexOf('payflow')>=0){
                    var urlConnect = PayPalLightboxConfig.setExpressCheckoutUk;
                }else if (link.indexOf('paypal')>=0){
                    var urlConnect = PayPalLightboxConfig.setExpressCheckout
                }
            }else{
                if (payment.currentMethod=='paypal_express'){
                    var urlConnect = PayPalLightboxConfig.setExpressCheckout
                }

                if (payment.currentMethod=='paypaluk_express'){
                    var urlConnect = PayPalLightboxConfig.setExpressCheckoutUk;
                }
            }


            paypal.checkout.initXO();
            $ji.support.cors = true;
            $ji.ajax({
                url: urlConnect,
                type: "GET",
                async: true,
                crossDomain: false,

                success: function (token) {

                    if (token.indexOf('cart') != -1  || token.indexOf('login')!= -1 || token.indexOf('onepage') != -1){
                        paypal.checkout.closeFlow();
                        setLocation(token);

                    }else{
                        var url = paypal.checkout.urlPrefix + token;
                        paypal.checkout.startFlow(url);
                    }

                },
                error: function (responseData, textStatus, errorThrown) {
                    alert("Error in ajax post"+responseData.statusText);
                    //Gracefully Close the minibrowser in case of AJAX errors
                    paypal.checkout.closeFlow();
                }
            });
        }
    });


};
var IWD=IWD||{};
IWD.LIPP = {
    config: null,
    lipp_enabled: false,
    init: function(){
        if (typeof(PayPalLightboxConfig)!="undefined"){
            this.config = PayPalLightboxConfig;
            if (this.config.isActive==1){
                this.initOPC();
            }
        }
    },

    initOPC: function(){
        IWD.LIPP.lipp_enabled = true;
        $j_opc(document).on('click', '.opc-wrapper-opc #checkout-payment-method-load .radio', function(e){
            var method = payment.currentMethod;
            if (method.indexOf('paypaluk_express')!=-1 || method.indexOf('paypal_express')!=-1){
                if (IWD.OPC.Checkout.config.comment!=="0"){
                    IWD.OPC.saveCustomerComment();
                }
            }
        });

    }
};

$j_opc(document).ready(function(){
    IWD.LIPP.init();
});