<?php

/**
 * PayU Update Info Block
 *
 * @copyright Copyright (c) 2011-2016 PayU
 * @license http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 */
class PayU_Account_Block_UpdateInfo extends Mage_Adminhtml_Block_System_Config_Form_Field
{
    /**
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getElementHtml(Varien_Data_Form_Element_Abstract $element)
    {
        $html = Mage::helper('payu_account')->__('You are currently using version') . ":<br><b>";
        $html .= Mage::getModel('payu_account/config')->getPluginVersion() . " " . Mage::helper('payu_account')->__('for') . " magento " . Mage::getModel('payu_account/config')->getMinimumMageVersion() . "+</b>";

        return $html;
    }
}
