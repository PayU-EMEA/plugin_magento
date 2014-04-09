<?php

/**
*	ver. 1.9.0
*	PayU Adminhtml Sales Order Controller
*	
*	@copyright  Copyright (c) 2011-2014 PayU
*	@license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/

include_once("Mage/Adminhtml/controllers/Sales/OrderController.php");

class PayU_Account_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController{
	
	
	/**
	 * Cancel 
	 */
	public function cancelPayUOrderAction(){
		
		$order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
		$payu = Mage::getModel('payu_account/payment');
		$session = Mage::getSingleton('adminhtml/session');
		
		if($payu->cancelOrder($order)){
			$session->addSuccess( Mage::helper('payu_account')->__('The order is awaiting for cancelation in PayU, please wait while status changes.') );
		}else{
			$session->addError( Mage::helper('payu_account')->__('There was a problem while canceling the order in PayU.') );
		}
		$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		
	}
	
	/**
	 * Reject 
	 */
	public function rejectPayUOrderAction(){
		
		$order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
		$payu = Mage::getModel('payu_account/payment');
		$session = Mage::getSingleton('adminhtml/session');

		if($payu->rejectOrder($order)){
			$session->addSuccess( Mage::helper('payu_account')->__('The order has been rejected in PayU.') );
		}else{
			$session->addError( Mage::helper('payu_account')->__('There was a problem while rejecting the order in PayU.') );
		}
		$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		
	}
	
	/**
	 *  
	 */
	public function completePayUOrderAction(){
		
	    $order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
		$payu = Mage::getModel('payu_account/payment');
		$session = Mage::getSingleton('adminhtml/session');

		if($payu->completeOrder($order)){
			$session->addSuccess(Mage::helper('payu_account')->__('The order is completing in PayU, please wait while status changes.'));
		}else{
			$session->addError(Mage::helper('payu_account')->__('There was a problem while completing the order in PayU.'));
		}
		$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		
	}

}