<?php


/**
 *	ver. 0.1.6.5.1
 *	PayU Environment Model
 *
 *	@copyright  Copyright (c) 2011-2012 PayU
 *	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
 */


class PayU_Account_Model_Environment
{

	/** @var string Production environment */
	const PRODUCTION	= 'secure';

	/** @var string Testing environment */
	const SANDBOX		= 'sandbox';

	public function toOptionArray() {
		return array(
			array(
                'value' => PayU_Account_Model_Environment::PRODUCTION,
                'label' => Mage::helper('payu_account')->__('No')
            ),
            array(
                'value' => PayU_Account_Model_Environment::SANDBOX,
                'label' => Mage::helper('payu_account')->__('Yes')
            )
        );
    }
}
