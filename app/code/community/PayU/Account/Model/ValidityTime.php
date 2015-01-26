<?php

/**
 *	ver. 1.9.0
 *	PayU Validity Time Model
 *
 *	@copyright  Copyright (c) 2011-2014 PayU
 *	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
 */


class PayU_Account_Model_ValidityTime
{

	public function toOptionArray() {
		
		$minutes = array(
		
			'86400' 	=> 	'1440 min (24h)',
			'43200' 	=> 	'720 min (12h)',
			'21600' 	=> 	'360 min (6h)',
			'3600' 	=> 	'60 min (1h)',
			'1800' 	=> 	'30 min',
		
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
