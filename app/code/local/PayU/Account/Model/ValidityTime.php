<?php


/**
 *	ver. 1.8
 *	PayU Validity Time Model
 *
 *	@copyright  Copyright (c) 2011-2012 PayU
 *	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
 */


class PayU_Account_Model_ValidityTime
{

	public function toOptionArray() {
		
		$minutes = array(
		
			'1440' 	=> 	'1440 min (24h)',
			'720' 	=> 	'720 min (12h)',
			'360' 	=> 	'360 min (6h)',
			'60' 	=> 	'60 min (1h)',
			'30' 	=> 	'30 min',
		
		);
		
		$options = array();		  
        
        foreach ($minutes as $code => $label) {
            $options[] = array(
               'value' => $code,
               'label' => $label
            );
        }
        
        return $options;

    }
}
