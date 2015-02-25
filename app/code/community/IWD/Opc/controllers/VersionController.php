<?php
class IWD_Opc_VersionController extends Mage_Core_Controller_Front_Action{
	
	public function indexAction(){
		$version = Mage::getConfig()->getModuleConfig("IWD_Opc")->version;
		echo 'IWD OPC Version: ' . $version;
		return;
	}
}