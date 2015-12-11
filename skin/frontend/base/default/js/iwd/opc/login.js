;
var IWD=IWD||{};

//define jquery
if(typeof($j_opc) == 'undefined' || $j_opc == undefined || !$j_opc){
	$j_opc = false;
	
	if(typeof($ji) != 'undefined' && $ji != undefined && $ji)
		$j_opc = $ji; // from iwd_all 2.x
	else{
		if(typeof(jQuery) != 'undefined' && jQuery != undefined && jQuery)
			$j_opc = jQuery;
	}	
}
///

IWD.Paypal = {
	Login : {
		init: function(){
			$j_opc('#topPayPalIn').click(function(event){
				event.preventDefault();
				IWD.Paypal.Login.showDialog($j_opc(this).attr('href'));
			});
			
			$j_opc('#login-with-paypal').click(function(event){
				event.preventDefault();
				IWD.Paypal.Login.showDialog($j_opc(this).attr('href'));
			});
		},
		
		showDialog: function(url){
			mywindow = window.open (url, "_PPIdentityWindow_", "location=1, status=0, scrollbars=0, width=400, height=550");
		}
	}
};

$j_opc(document).ready(function(){
	IWD.Paypal.Login.init();
});