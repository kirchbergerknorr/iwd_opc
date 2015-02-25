<?php

class IWD_Opc_Model_System_Config_Source_Terms{
    /**
     * Return array of carriers.
     * If $isActiveOnlyFlag is set to true, will return only active carriers
     *
     * @param bool $isActiveOnlyFlag
     * @return array
     */
    public function toOptionArray($isActiveOnlyFlag=false)
    {
         return array(
            array('value' => 1, 'label'=>Mage::helper('adminhtml')->__('Popup')),
            array('value' => 0, 'label'=>Mage::helper('adminhtml')->__('Inline')),
        );

    }
}
