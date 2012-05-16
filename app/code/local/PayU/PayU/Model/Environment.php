<?php


/**
 *	ver. 0.1.5
 *	PayU Environment Model
 *
 *	@copyright  Copyright (c) 2011-2012 PayU
 *	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://twitter.com/openpayu
 */


class PayU_PayU_Model_Environment
{

	/** @var string Production environment */
	const PRODUCTION	= 'secure';

	/** @var string Testing environment */
	const SANDBOX		= 'sandbox';

	public function toOptionArray() {
		return array(
		array(
                'value' => PayU_PayU_Model_Environment::PRODUCTION,
                'label' => Mage::helper('payu')->__('Production')
            ),
            array(
                'value' => PayU_PayU_Model_Environment::SANDBOX,
                'label' => 'Sandbox'
            )
        );
    }
}
