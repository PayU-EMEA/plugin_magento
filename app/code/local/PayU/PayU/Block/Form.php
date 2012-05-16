<?php

/**
*	ver. 0.1.5
*	PayU Form Block
*   Payment
*	
*	@copyright  Copyright (c) 2011-2012 PayU
*	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
*	http://www.payu.com
*	http://twitter.com/openpayu
*/


class PayU_PayU_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = 'payu';

    
    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        $this->setTemplate('payu/form.phtml');
        $this->setMethodTitle('');
        $this->setMethodLabelAfterHtml('<img src="'.Mage::getModel('payu/config')->getThumbnailSrc().'" height="20" alt="PayU"/> '.Mage::helper('payu')->__('Credit Card or E-transfer'));
        
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
}