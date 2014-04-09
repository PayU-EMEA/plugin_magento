<?php

/**
*	ver. 1.8.1
*	PayU Adminhtml Sales Order View
*	
*	@copyright  Copyright (c) 2011-2014 PayU
*	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
*	http://www.payu.com
*	http://www.openpayu.com
*	http://twitter.com/openpayu
*/

class PayU_Account_Block_Adminhtml_Sales_Order_View extends Mage_Adminhtml_Block_Sales_Order_View {

    // constructor
    public function  __construct() {

        parent::__construct();

        if($this->_isAllowedAction('review_payment') && $this->getOrder()->getPayment()->getMethod() == 'payu_account' && $this->getOrder()->getStatus() == 'pending_payment' && (int)Mage::getStoreConfig('payment/payu_account/selfreturn') == 0){
            $this->_addButton('payu-complete', array(
                'label'     => Mage::helper('payu_account')->__('Complete PayU payment'),
                'onclick'   => 'setLocation(\'' . $this->getCompletePaymentUrl() . '\')',
                'class'     => ''
            ), 0, 100, 'header', 'header');

            $this->_addButton('payu-cancel', array(
                'label'     => Mage::helper('payu_account')->__('Cancel PayU payment'),
                'onclick'   => 'setLocation(\'' . $this->getCancelPaymentUrl() . '\')',
                'class'     => ''
            ), 0, 200, 'header', 'header');
        }
    }
    
    // rejecting the PayU payment
    public function getRejectPaymentUrl()
    {
        return $this->getUrl('*/sales_order/rejectPayUOrder');
    }

    // canceling the PayU payment
    public function getCancelPaymentUrl()
    {
        return $this->getUrl('*/sales_order/cancelPayUOrder');
    }

    // completing the PayU payment
    public function getCompletePaymentUrl()
    {
        return $this->getUrl('*/sales_order/completePayUOrder');
    }
}
