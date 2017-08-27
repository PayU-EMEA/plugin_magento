<?php

class Payu_Account_Block_Adminhtml_Block_System_Config_Form_Fieldset extends Mage_Adminhtml_Block_System_Config_Form_Fieldset
{

    /**
     * Return header title part of html for payment solution
     *
     * @param Varien_Data_Form_Element_Abstract $element
     * @return string
     */
    protected function _getHeaderTitleHtml($element)
    {
        $html = '<div class="entry-edit-head collapseable" ><a id="' . $element->getHtmlId()
                    . '-head" href="#" style="line-height: 21px" onclick="Fieldset.toggleCollapse(\'' . $element->getHtmlId() . '\', \''
                    . $this->getUrl('*/*/state') . '\'); return false;">';

        $html .= ' <img src="'.$this->getSkinUrl('images/payu/payu_logo.png').'" height="21" style="vertical-align: bottom; margin-right: 5px;"/> ';
        $html .= $element->getLegend();

        $html .= '</a></div>';
        return $html;
    }
}
