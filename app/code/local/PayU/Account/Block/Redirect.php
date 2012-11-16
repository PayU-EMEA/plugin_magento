<?php

/**
*	ver. 0.1.6.5.1
*	PayU Payment Redirect Block
*   Payment
*
*	@copyright  Copyright (c) 2011-2012 PayU
*	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/

class PayU_Account_Block_Redirect extends Mage_Core_Block_Abstract
{
    
	protected $_order = null;
	
	protected $_allShippingMethods = null;
    
    /**
     * (non-PHPdoc)
     * @see magento1/app/code/core/Mage/Core/Block/Mage_Core_Block_Abstract::_toHtml()
     */
    protected function _toHtml()
    {
    	/**
    	 * Fetching current payment
    	 * 
    	 * @var PayU_Account_Model_Payment
    	 */
        $payment = Mage::getModel('payu_account/payment');
         
        /**
         * Payment form
         * @var Varien_Data_Form
         */
        $form = new Varien_Data_Form();
        
        /**
         * Setting the redirect info
         * @var array
         */
        $redirectData = $payment->orderCreateRequest($this->_order,$this->_allShippingMethods);
        
        $form->setAction($redirectData['url'])
            ->setId('payu_checkout')
            ->setName('payu_checkout')
            ->setMethod('GET')
            ->setUseContainer(true);
        
        $form->addField('oauth_token', 'hidden', array('name'=>'oauth_token', 'value'=>$redirectData['oauthToken']));
        $form->addField('sessionId', 'hidden', array('name'=>'sessionId', 'value'=>$redirectData['sessionId']));
        $form->addField('lang', 'hidden', array('name'=>'lang', 'value'=>$redirectData['lang']));

        $html = '<html><body>';
        $html.= $form->toHtml();
        $html.= '<script type="text/javascript">document.getElementById("payu_checkout").submit();</script>';
        $html.= '</body></html>';

        return $html;
    }
    
    public function setAllShippingMethods($allShippingMethods){
    	$this->_allShippingMethods = $allShippingMethods;
        return $this;
    }
    
    public function setOrder($order) {
        $this->_order = $order;
        return $this;
    }
}
