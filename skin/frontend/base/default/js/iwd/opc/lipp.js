//;
//
////define jquery
//if(typeof($j_opc) == 'undefined' || $j_opc == undefined || !$j_opc){
//	$j_opc = false;
//
//	if(typeof($ji) != 'undefined' && $ji != undefined && $ji)
//		$j_opc = $ji; // from iwd_all 2.x
//	else{
//		if(typeof(jQuery) != 'undefined' && jQuery != undefined && jQuery)
//			$j_opc = jQuery;
//	}
//}
/////
//
//var IWD=IWD||{};
//
//IWD.LIPP = {
//	config: null,
//	lipp_enabled: false,
//
//	init: function(){
//		if (typeof(lippConfig)!="undefined"){
//			this.config = $j_opc.parseJSON(lippConfig);
//			if (this.config.paypalLightBoxEnabled==true){
//				this.initOPC();
//				this.initOnCart();
//			}
//		}
//	},
//
//	initOPC: function(){
//
//		IWD.LIPP.lipp_enabled = true;
//
//		$j_opc(document).on('click', '.opc-wrapper-opc #checkout-payment-method-load .radio', function(e){
//			var method = payment.currentMethod;
//			if (method.indexOf('paypaluk_express')!=-1 || method.indexOf('paypal_express')!=-1){
//				if (IWD.OPC.Checkout.config.comment!=="0"){
//					IWD.OPC.saveCustomerComment();
//				}
//			}
//		});
//
//	},
//
//	redirectPayment: function(){
//		PAYPAL.apps.Checkout.startFlow(IWD.LIPP.config.baseUrl + 'onepage/express/start');
//		return false;
//	},
//
//	initOnCart: function(){
//		$j_opc('.checkout-types .paypal-logo a, .opc-menu .paypal-logo a').click(function(e){
//			e.preventDefault();
//			PAYPAL.apps.Checkout.startFlow(IWD.LIPP.config.baseUrl + 'onepage/express/start');
//		});
//	}
//};
//
//$j_opc(document).ready(function(){
//	IWD.LIPP.init();
//});