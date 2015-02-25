;
var IWD=IWD||{};
$j = jQuery;
IWD.Paypal = {
	Login : {
		init: function(){
			$j('#topPayPalIn').click(function(event){
				event.preventDefault();
				IWD.Paypal.Login.showDialog($j(this).attr('href'));
			});
			
			$j('#login-with-paypal').click(function(event){
				event.preventDefault();
				IWD.Paypal.Login.showDialog($j(this).attr('href'));
			});
		},
		
		showDialog: function(url){
			mywindow = window.open (url, "_PPIdentityWindow_", "location=1, status=0, scrollbars=0, width=400, height=550");
		}
	}
};

$j(document).ready(function(){
	IWD.Paypal.Login.init();
});