<?php

/**
 *	ver. 1.8
 *	PayU Thumbnail Model
 *
 *	@copyright  Copyright (c) 2011-2012 PayU
 *	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
 */

class PayU_Account_Model_Thumbnail
{
    public function toOptionArray()
    {
            
    	$thumbnails = Mage::getModel('payu_account/config')->getGoods('media_logos');
    	
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
