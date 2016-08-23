<?php

/**
 * PayU Form Block
 *
 * @copyright Copyright (c) 2011-2016 PayU
 * @license http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 */
class PayU_Account_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = 'payu_account';


    /**
     * Prepare layout.
     * Add files to use PayU
     *
     * @return PayU_Account_Block_Form
     */
    protected function _prepareLayout()
    {
        if ($head = $this->getLayout()->getBlock('head')) {
            $head->addCss('css/payu/payu.css');
        }
        return parent::_prepareLayout();
    }

    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        $this->setTemplate('payu_account/form.phtml');
        $this->setMethodTitle($this->__('Pay with PayU'));
        $this->setMethodLabelAfterHtml('<img src="' . $this->getPayuLogo() . '" alt="PayU" class="formPayuLogo" />');

        return parent::_construct();
    }

    /**
     * Payment method code getter
     * @return string
     */
    public function getMethodCode()
    {
        return $this->_methodCode;
    }

    /**
     * @return string
     */
    private function getPayuLogo()
    {
        return $this->getSkinUrl('images/payu/payu_logo.png');
    }
}
