<?php

/**
*	ver. 0.1.5
*	PayU Logo Model
*	
*	@copyright  Copyright (c) 2011-2012 PayU
*	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
*	http://www.payu.com
*	http://twitter.com/openpayu
*/

/*
 * CHANGE_LOG:
 *   2012-03-08:
 *     - change from logo class to Advertisement
 */

class PayU_PayU_Model_Advertisement
{

    public function toOptionArray()
    {
            
    	$advertisements = Mage::getModel('payu/config')->getGoods('media_adverts_skyscraper');
            
        $options = array();		  
        
        foreach ($advertisements as $code => $label) {
            $options[] = array(
               'value' => $label,
               'label' => $label
            );
        }
        return $options;
    }
}
