<?php
class IWD_Opc_Block_Paypal_Shortcut extends Mage_Paypal_Block_Express_Shortcut{

    protected function _beforeToHtml(){
        parent::_beforeToHtml();
        $list = Mage::getSingleton('core/session')->getIdsButtons();
        if (!is_array($list)){
            $list= array();
        }
        $id = $this->getShortcutHtmlId();
        if (!empty($id)){
            $list[] = $id;
        }
        $list = array_unique($list);
        Mage::getSingleton('core/session')->setIdsButtons($list);
    }

}