<?php

/**
*	ver. 0.1.5
*	PayU Adminhtml Sales Order Controller
*	
*	@copyright  Copyright (c) 2011-2012 PayU
*	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
*	http://www.payu.com
*	http://twitter.com/openpayu
*/

include_once("Mage/Adminhtml/controllers/Sales/OrderController.php");

class PayU_PayU_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController{
	
	
	/**
	 * Cancel 
	 */
	public function cancelPayUOrderAction(){
		
		$order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
		
		$payu = Mage::getModel('payu/payment');

		$session = Mage::getSingleton('adminhtml/session');
		
		if($payu->cancelOrder($order)->getSuccess()){
			$session->addSuccess( Mage::helper('payu')->__('The order has been cancelled in PayU.') );
		}else{
			$session->addError( Mage::helper('payu')->__('There was a problem while cancelling the order in PayU.') );
		}
		$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		
	}
	
	/**
	 * Reject 
	 */
	public function rejectPayUOrderAction(){
		
		$order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
		
		$payu = Mage::getModel('payu/payment');

		$session = Mage::getSingleton('adminhtml/session');
		
		if($payu->rejectOrder($order)->getSuccess()){
			$session->addSuccess( Mage::helper('payu')->__('The order has been rejected in PayU.') );
		}else{
			$session->addError( Mage::helper('payu')->__('There was a problem while rejecting the order in PayU.') );
		}
		$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		
	}
	
	/**
	 *  
	 */
	public function completePayUOrderAction(){
		$order = Mage::getModel('sales/order')->load(Mage::app()->getRequest()->getParam('order_id'));
		
		$payu = Mage::getModel('payu/payment');
		$session = Mage::getSingleton('adminhtml/session');
		
		if($payu->completeOrder($order)->getSuccess()){
			$session->addSuccess(Mage::helper('payu')->__('The order is completing in PayU, please wait while status changes.'));
		}else{
			$session->addError(Mage::helper('payu')->__('There was a problem while completing the order in PayU.'));
		}
		$this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
		
	}

}