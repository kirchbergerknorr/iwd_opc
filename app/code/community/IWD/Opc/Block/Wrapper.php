<?php
class IWD_Opc_Block_Wrapper extends  Mage_Core_Block_Template{
	
	
	const XML_PATH_DEFAULT_SHIPPING = 'opc/default/shipping';
	
	const XML_PATH_GEO_COUNTRY = 'opc/geo/country';
	
	const XML_PATH_GEO_CITY = 'opc/geo/city';
	
	
	
	/**
	 * Get one page checkout model
	 *
	 * @return Mage_Checkout_Model_Type_Onepage
	 */
	public function getOnepage(){
		return Mage::getSingleton('checkout/type_onepage');
	}
	
	protected function _getReviewHtml(){
		//clear cache aftr change collection - if no magento can't find product in review block
		Mage::app()->getCacheInstance()->cleanType('layout');

		$layout = $this->getLayout();
		$update = $layout->getUpdate();
		$update->load('checkout_onepage_review');
		$layout->generateXml();
		$layout->generateBlocks();
		$review = $layout->getBlock('root');
		$review->setTemplate('opc/onepage/review/info.phtml');
		
		return $review->toHtml();
	}
	
	protected function _getCart(){
		return Mage::getSingleton('checkout/cart');
	}

	
	public function getJsonConfig() {
	
		$config = array ();
		$params = array (
				'_secure' => true
		);
		
		$base_url = Mage::getBaseUrl('link', true);

		// protocol for ajax urls should be the same as for current page
		$http_protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS']==='on')?'https':'http';
		if ($http_protocol == 'https')
			$base_url = str_replace('http:', 'https:', $base_url);
		else
			$base_url = str_replace('https:', 'http:', $base_url);
		//////

		$config['baseUrl'] = $base_url;
		$config['isLoggedIn'] = (int) Mage::getSingleton('customer/session')->isLoggedIn();
		$config['comment'] = Mage::helper('opc')->isShowComment();
		$config['paypalLightBoxEnabled'] = Mage::helper('opc')->getPayPalLightboxEnabled();

		return Mage::helper ( 'core' )->jsonEncode ( $config );
	}
	
	protected function validate_color($color){
		$correct_color = '';
		
		$pattern = "/([^0-9abcdefABCDEF])/";
		if(!empty($color)){
			$color = str_replace('#', '', $color);
			if(!preg_match($pattern, $color)){
				if(strlen($color) == 3 || strlen($color) == 6){
					$correct_color = '#'.$color;
				}
			}
		}
		return $correct_color;
	}
	
	public function getDesignStyles(){
		$styles = '';

		$styles .= $this->getDesignStylesFor_PlaceOrderButton();
		$styles .= $this->getDesignStylesFor_LoginButton();
		$styles .= $this->getDesignStylesFor_OtherButton();

		if(!empty($styles)) {
			$styles = "<style>{$styles}</style>";
		}
		
		return $styles;
	}

	protected function getDesignStylesFor_PlaceOrderButton()
	{
		$styles = "";

		$plbgcolor = Mage::getStoreConfig('opc/design/plbgcolor');
		$plovercolor = Mage::getStoreConfig('opc/design/plovercolor');
		$pltextcolor = Mage::getStoreConfig('opc/design/pltextcolor');
		$plhovertextcolor = Mage::getStoreConfig('opc/design/plhovertextcolor');

		$plbgcolor = $this->validate_color($plbgcolor);
		$plovercolor = $this->validate_color($plovercolor);
		$pltextcolor = $this->validate_color($pltextcolor);
		$plhovertextcolor = $this->validate_color($plhovertextcolor);

		if(!empty($plbgcolor)){
			$styles.=".opc-wrapper-opc .btn-checkout span, .opc-wrapper-opc .btn-checkout span span";
			$styles.="{background-color:{$plbgcolor} !important;}";
			// setup color for disabled place order.
			$hex = str_replace('#', '', $plbgcolor);
			if(strlen($hex)==3){
				$p1 = substr($hex, 0, 1); $p1 = $p1.$p1;
				$p2 = substr($hex, 1, 1); $p2 = $p2.$p2;
				$p3 = substr($hex, 2, 1); $p3 = $p3.$p3;
			} else {
				$p1 = substr($hex, 0, 2);
				$p2 = substr($hex, 2, 2);
				$p3 = substr($hex, 4, 2);
			}
			$p1 = ceil(hexdec($p1)/2);
			$p2 = ceil(hexdec($p2)/2);
			$p3 = ceil(hexdec($p3)/2);
			$styles.=".opc-wrapper-opc .btn-checkout.button-disabled span{background-color:rgba({$p1},{$p2},{$p3}, .8) !important;}";
		}
		if(!empty($plovercolor)){
			$styles.=".opc-wrapper-opc .btn-checkout:hover span, .opc-wrapper-opc .btn-checkout:hover span span";
			$styles.="{background-color:{$plovercolor} !important;}";
		}
		if(!empty($pltextcolor)){
			$styles.=".opc-wrapper-opc .btn-checkout span span";
			$styles.="{color:{$pltextcolor};}";
		}
		if(!empty($plhovertextcolor)){
			$styles.=".opc-wrapper-opc .btn-checkout:hover span span";
			$styles.="{color:{$plhovertextcolor} !important;}";
		}

		return $styles;
	}

	protected function getDesignStylesFor_OtherButton()
	{
		$styles = "";

		$btnbgcolor = Mage::getStoreConfig('opc/design/btnbgcolor');
		$btnovercolor = Mage::getStoreConfig('opc/design/btnovercolor');
		$btntextcolor = Mage::getStoreConfig('opc/design/btntextcolor');
		$btnhovertextcolor = Mage::getStoreConfig('opc/design/btnhovertextcolor');

		$btnbgcolor = $this->validate_color($btnbgcolor);
		$btnovercolor = $this->validate_color($btnovercolor);
		$btntextcolor = $this->validate_color($btntextcolor);
		$btnhovertextcolor = $this->validate_color($btnhovertextcolor);

		if(!empty($btnbgcolor)){
			$styles.=".opc-wrapper-opc .btn span, .opc-wrapper-opc .btn span span,";
			$styles.=".opc-wrapper-opc .discount-block .button span, .opc-wrapper-opc .discount-block .button span span,";
			$styles.=".opc-wrapper-opc .payment-block dt,";
			$styles.=".opc-wrapper-opc .giftcard .button span, .opc-wrapper-opc .giftcard .button span span,";
			$styles.=".opc-messages-action .button span, .opc-messages-action .button span span,";
			$styles.=".review-menu-block a.review-total,";
			$styles.=".opc-wrapper-opc .opc-login-trigger";
			$styles.="{background-color:{$btnbgcolor} !important;}";
			$styles.=".opc-wrapper-opc .opc-review-actions .view-agreement:hover{color:{$btnbgcolor} !important;}";

			$styles.=".review-menu-block a.review-total, .expand_plus";
			$styles.="{color:{$btnbgcolor} !important;}";
		}
		if(!empty($btnovercolor)){
			$styles.=".opc-wrapper-opc .btn:hover span, .opc-wrapper-opc .btn:hover span span,";
			$styles.=".opc-wrapper-opc .discount-block .button:hover span, .opc-wrapper-opc .discount-block .button:hover span span,";
			$styles.=".opc-wrapper-opc .payment-block dt:hover, .opc-wrapper-opc .payment-block dt.active,";
			$styles.=".opc-messages-action .button:hover span, .opc-messages-action .button:hover span span,";
			$styles.=".opc-wrapper-opc .opc-login-trigger:hover";
			$styles.="{background-color:{$btnovercolor} !important;}";

			$styles.=".review-menu-block a.review-total:hover, .review-menu-block a.review-total.open";
			$styles.="{background-color:{$btnovercolor} !important;}";
			$styles.=".review-menu-block .polygon{border-top-color:{$btnovercolor} !important;}";

			$styles.=".discount-block h3:hover .expand_plus, .signature-block h3:hover .expand_plus, .comment-block h3:hover .expand_plus, .giftcard h3:hover .expand_plus,";
			$styles.=".discount-block h3.open-block .expand_plus, .signature-block h3.open-block .expand_plus, .comment-block h3.open-block .expand_plus, .giftcard h3.open-block .expand_plus";
			$styles.="{color:{$btnovercolor} !important;}";
		}
		if(!empty($btntextcolor)){
			$styles.=".opc-wrapper-opc .discount-block .button span span,";
			$styles.=".opc-wrapper-opc .payment-block dt label,";
			$styles.=".opc-wrapper-opc .btn span span,";
			$styles.=".opc-wrapper-opc a:hover,";
			$styles.=".opc-wrapper-opc .giftcard .button span span,";
			$styles.=".opc-wrapper-opc .giftcard .check-gc-status span,";
			$styles.=".opc-messages-action .button span span,";
			$styles.=".review-menu-block a.review-total, .review-menu-block a.review-total.open,";
			$styles.=".review-menu-block a.review-total span, .review-menu-block a.review-total.open span,";
			$styles.=".opc-wrapper-opc .opc-login-trigger:hover";
			$styles.="{color:{$btntextcolor} !important;}";
		}
		if(!empty($btnhovertextcolor)){
			$styles.=".opc-wrapper-opc .discount-block .button:hover span span,";
			$styles.=".opc-wrapper-opc .payment-block dt:hover label, .opc-wrapper-opc .payment-block dt.active label,";
			$styles.=".opc-wrapper-opc .btn:hover span span,";
			$styles.=".opc-messages-action .button:hover span, .opc-messages-action .button:hover span span,";
			$styles.=".review-menu-block a.review-total:hover, .review-menu-block a.review-total.open,";
			$styles.=".review-menu-block a.review-total:hover span, .review-menu-block a.review-total.open span,";
			$styles.=".opc-wrapper-opc a:hover,";
			$styles.="h3:hover .expand_plus";
			$styles.="{color:{$btnhovertextcolor} !important;}";
		}

		return $styles;
	}

	protected function getDesignStylesFor_LoginButton()
	{
		$styles = "";

		$bgcolor = Mage::getStoreConfig('opc/design/loginbgcolor');
		$overcolor = Mage::getStoreConfig('opc/design/loginovercolor');
		$textcolor = Mage::getStoreConfig('opc/design/logintextcolor');
		$hovertextcolor = Mage::getStoreConfig('opc/design/loginhovertextcolor');

		$bgcolor = $this->validate_color($bgcolor);
		$overcolor = $this->validate_color($overcolor);
		$textcolor = $this->validate_color($textcolor);
		$hovertextcolor = $this->validate_color($hovertextcolor);

		if(!empty($bgcolor)){
			$styles.=".opc-wrapper-opc .btn-primary.btn span span,";
			$styles.=".opc-wrapper-opc .opc-menu .opc-login-trigger";
			$styles.="{background-color:{$bgcolor} !important;}";
		}
		if(!empty($overcolor)){
			$styles.=".opc-wrapper-opc .btn-primary.btn:hover span span,";
			$styles.=".opc-wrapper-opc .opc-menu .opc-login-trigger:hover";
			$styles.="{background-color:{$overcolor} !important;}";
		}
		if(!empty($textcolor)){
			$styles.=".opc-wrapper-opc .btn-primary.btn span span,";
			$styles.=".opc-wrapper-opc .opc-menu .opc-login-trigger";
			$styles.="{color:{$textcolor} !important;}";
		}
		if(!empty($hovertextcolor)){
			$styles.=".opc-wrapper-opc .btn-primary.btn:hover span span,";
			$styles.=".opc-wrapper-opc .opc-menu .opc-login-trigger:hover";
			$styles.="{color:{$hovertextcolor} !important;}";
		}

		return $styles;
	}
}