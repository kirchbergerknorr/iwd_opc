;
//DUMMY FOR EE CHECKOUT
var checkout =  {
		steps : new Array("login", "billing", "shipping", "shipping_method", "payment", "review"),
		
		gotoSection: function(section){
			IWD.OPC.backToOpc();
		},
		accordion:{
			
		}
};


IWD.OPC.prepareExtendPaymentForm =  function(){
	$j_opc('.opc-col-left').hide();
	$j_opc('.opc-col-center').hide();
	$j_opc('.opc-col-right').hide();
	$j_opc('.opc-menu p.left').hide();	
	$j_opc('#checkout-review-table-wrapper').hide();
	$j_opc('#checkout-review-submit').hide();
	
	$j_opc('.review-menu-block').addClass('payment-form-full-page');
	
};

IWD.OPC.backToOpc =  function(){
	$j_opc('.opc-col-left').show();
	$j_opc('.opc-col-center').show();
	$j_opc('.opc-col-right').show();
	$j_opc('#checkout-review-table-wrapper').show();
	$j_opc('#checkout-review-submit').show();
	
	
	
	//hide payments form
	$j_opc('#payflow-advanced-iframe').hide();
	$j_opc('#payflow-link-iframe').hide();
	$j_opc('#hss-iframe').hide();

	
	$j_opc('.review-menu-block').removeClass('payment-form-full-page');
	
	IWD.OPC.saveOrderStatus = false;
	
};



IWD.OPC.Plugin = {
		
		observer: {},
		
		
		dispatch: function(event, data){
				
			
			if (typeof(IWD.OPC.Plugin.observer[event]) !="undefined"){
				
				var callback = IWD.OPC.Plugin.observer[event];
				callback(data);
				
			}
		},
		
		event: function(eventName, callback){
			IWD.OPC.Plugin.observer[eventName] = callback;
		}
};

/** 3D Secure Credit Card Validation - CENTINEL **/
IWD.OPC.Centinel = {
	init: function(){
		IWD.OPC.Plugin.event('savePaymentAfter', IWD.OPC.Centinel.validate);
	},
	
	validate: function(){
		var c_el = $j_opc('#centinel_authenticate_block');
		if(typeof(c_el) != 'undefined' && c_el != undefined && c_el){
			if(c_el.attr('id') == 'centinel_authenticate_block'){
				IWD.OPC.prepareExtendPaymentForm();
			}
		}
	},
	
	success: function(){
		var exist_el = false;
		if(typeof(c_el) != 'undefined' && c_el != undefined && c_el){
			if(c_ell.attr('id') == 'centinel_authenticate_block'){
				exist_el = true;
			}
		}
		
		if (typeof(CentinelAuthenticateController) != "undefined" || exist_el){
			IWD.OPC.backToOpc();
		}
	}
	
};


function toggleContinueButton(){}//dummy

$j_opc(document).ready(function(){
	IWD.OPC.Centinel.init();
});
