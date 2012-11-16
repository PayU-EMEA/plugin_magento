<?php

/**
*	ver. 0.1.6.5.1
*	PayU Logo Model
*	
*	@copyright  Copyright (c) 2011-2012 PayU
*	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/

class PayU_Account_Model_Advertisement
{

    public function toOptionArray()
    {
            
    	$advertisements = Mage::getModel('payu_account/config')->getGoods('media_adverts_skyscraper');
            
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
