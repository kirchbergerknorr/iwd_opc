<?php
class IWD_Opc_Block_Links extends Mage_Core_Block_Template{
    

    /**
     * Add link on checkout page to parent block
     *
     * @return Mage_Checkout_Block_Links
     */
    public function addCheckoutLink(){
    	
        

        $parentBlock = $this->getParentBlock();
        $text = $this->__('Checkout');
        if (Mage::helper('opc')->isEnable()){
        	$parentBlock->addLink($text, 'onepage', $text, true, array('_secure'=>true), 60, null, 'class="top-link-checkout"');
        }else{
        	$parentBlock->addLink($text, 'checkout', $text, true, array('_secure'=>true), 60, null, 'class="top-link-checkout"');
        }
        
        return $this;
    }
}
