<?php

/**
*	ver. 1.9.0
*	PayU BeforeSummary Redirection Block
*	Payment
*	
*	@copyright  Copyright (c) 2011-2014 PayU
*	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/

class PayU_Account_Block_BeforeSummary extends Mage_Core_Block_Abstract
{
    protected $_order = null;
    
    /**
     * (non-PHPdoc)
     * @see magento/app/code/core/Mage/Core/Block/Mage_Core_Block_Abstract::_toHtml()
     */
    protected function _toHtml()
    {
        $payment = Mage::getModel('payu_account/payment');
         
        $form = new Varien_Data_Form();
        
        $redirectData = $payment->beforeSummary();
        
        $form->setAction($redirectData['url'])
            ->setId('payu_checkout')
            ->setName('payu_checkout')
            ->setMethod('GET')
            ->setUseContainer(true);
        
        $form->addField('sessionId', 'hidden', array('name' => 'sessionId', 'value' => $redirectData['sessionId']));
        $form->addField('oauth_token', 'hidden', array('name' => 'oauth_token', 'value' => $redirectData['oauth_token']));
        $form->addField('client_id', 'hidden', array('name' => 'client_id', 'value' => $redirectData['client_id']));
        
        $html = '<html><body>';
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("payu_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
    
    public function setOrder($order) {
        $this->_order = $order;
        return $this;
    }
}
