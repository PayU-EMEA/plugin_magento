<?php

/**
 *	ver. 0.1.5
 *	PayU Thumbnail Model
 *
 *	@copyright  Copyright (c) 2011-2012 PayU
 *	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://twitter.com/openpayu
 */

class PayU_PayU_Model_Thumbnail
{
    public function toOptionArray()
    {
            
    	$thumbnails = Mage::getModel('payu/config')->getGoods('media_logos');
    	
        $options = array();		  
        
        foreach ($thumbnails as $label) {
            $options[] = array(
               'value' => $label,
               'label' => $label
            );
        }
        return $options;
    }
}
