<?php

/**
*	ver. 0.1.6.5.1
*	PayU Form Block
*   Payment
*	
*	@copyright  Copyright (c) 2011-2012 PayU
*	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/


class PayU_Account_Block_Form extends Mage_Payment_Block_Form
{
    /**
     * Payment method code
     * @var string
     */
    protected $_methodCode = 'payu_account';

    
    /**
     * Set template and redirect message
     */
    protected function _construct()
    {
        $this->setTemplate('payu_account/form.phtml');
        $this->setMethodTitle('');
        $this->setMethodLabelAfterHtml('<img src="'.Mage::getModel('payu_account/config')->getThumbnailSrc().'" height="20" alt="PayU"/> '.Mage::helper('payu_account')->__('PayU account'));
        
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