<?php

/**
 *	ver. 1.9.0
 *	PayU One Step Checkout Block
 *
 *	@copyright  Copyright (c) 2011-2014 PayU
 *	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
 */

class PayU_Account_Block_Onestep_Checkout extends Mage_Core_Block_Template
{

	/** @var config variable */
	protected $_config;

	/** @var string Start One Step action  */
	protected $_startAction = 'newOneStep';

	/** @var bool Whether the block should be eventually rendered */
	protected $_shouldRender = true;

	/** @var string Express checkout model factory name */
	protected $_checkoutType = 'payu_account/express_checkout';

	/**
	 * (non-PHPdoc)
	 * @see magento/app/code/core/Mage/Core/Block/Mage_Core_Block_Template::_toHtml()
	 */
	protected function _toHtml()
	{


		if($this->getRequest()->getParam('submitCustomCheckout'))
		{
			$billingAddress = $this->getRequest()->getParam('billing');
			$billingAddress['use_for_shipping'] = 0;
			$shippingAddress = $this->getRequest()->getParam('shipping');
		}
		 
		 
		$this->_config = Mage::getModel('payu_account/config');
		$this->setButtonSrc($this->_config->getButtonSrc());
		$this->setIsOneStepCheckoutEnabled($this->_config->getIsOneStepCheckoutEnabled());
		$this->setIsOneStepCheckoutAvailable($this->_config->getIsOneStepCheckoutAvailable());
    	$this->setCheckoutUrl($this->_config->getBaseUrl().$this->_startAction);
    	
        return parent::_toHtml();
    }
}