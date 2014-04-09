<?php

/**
*	ver. 1.9.0
*	PayU Advertisement Block
*   Payment
*	
*	@copyright  Copyright (c) 2011-2014 PayU
*	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/

class PayU_Account_Block_Advertisement extends Mage_Core_Block_Template
{
	/**
	 * Redirection after clicking the advertisement
	 * 
	 * @var string
	 */
	protected $_redirectUrl = 'http://www.payu.pl';

	/**
	 * (non-PHPdoc)
	 * @see magento/app/code/core/Mage/Core/Block/Mage_Core_Block_Template::_toHtml()
	 */
    protected function _toHtml()
    {
    	/**
    	 * setting the advertisement source url
    	 */
    	$this->setAdvertisementSrc(Mage::getModel('payu_account/config')->getAdvertisementSrc());
    	
    	/**
    	 * setting the redirect url
    	 */
    	$this->setRedirectUrl($this->_redirectUrl);

        return parent::_toHtml();
    }
}
