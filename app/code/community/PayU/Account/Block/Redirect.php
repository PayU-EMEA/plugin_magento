<?php

/**
 * ver. 1.9.0
 * PayU Payment Redirect Block
 * Payment
 *
 * @copyright Copyright (c) 2011-2014 PayU
 * @license http://opensource.org/licenses/GPL-3.0 Open Software License (GPL 3.0)
 *          http://www.payu.com
 *          http://www.openpayu.com
 *          http://twitter.com/openpayu
 *         
 */

class PayU_Account_Block_Redirect extends Mage_Core_Block_Abstract {
    
    protected $_order = null;
    
    protected $_allShippingMethods = null;
    
    /**
     * (non-PHPdoc)
     * 
     * @see magento1/app/code/core/Mage/Core/Block/Mage_Core_Block_Abstract::_toHtml()
     */
    protected function _toHtml() {
        /**
         * Fetching current payment
         *
         * @var PayU_Account_Model_Payment
         */
        $payment = Mage::getModel ( 'payu_account/payment' );
        
        /**
         * Setting the redirect info
         * 
         * @var array
         */
        $redirectData = $payment->orderCreateRequest ( $this->_order, $this->_allShippingMethods );
        
        $html = '<html><body>';
        $html .= '<script type="text/javascript">window.location.replace("' . urldecode ( $redirectData ['redirectUri'] ) . '");</script>';
        $html .= '</body></html>';
        
        return $html;
    }
    
    public function setAllShippingMethods($allShippingMethods) {
        $this->_allShippingMethods = $allShippingMethods;
        return $this;
    }
    
    public function setOrder($order) {
        $this->_order = $order;
        return $this;
    }
}
