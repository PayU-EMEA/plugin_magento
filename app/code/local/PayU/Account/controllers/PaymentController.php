<?php

/**
 *	ver. 0.1.6.1
 *	PayU Standard Payment Controller
 *
 *	@copyright  Copyright (c) 2011-2012 PayU
 *	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
 */


class PayU_Account_PaymentController extends Mage_Core_Controller_Front_Action {

	protected $_session = null;
	protected $_order = null;
	protected $_payment = null;

	protected $_allShippingRates = null;

	/**
	 * Initializes new One Step Checkout
	 */
	public function newOneStepAction() {
		
		$this->setSession();
		$this->_order = Mage::getModel('payu_account/payment')->newOneStep();
		$this->_redirect('payu_account/payment/new');

	}

	/**
	 * Create invoice
	 *
	 * @return Mage_Sales_Model_Order_Invoice
	 */
	protected function _initInvoice()
	{
		$items = array();
		foreach ($this->_order->getAllItems() as $item) {
			$items[$item->getId()] = $item->getQtyOrdered();
		}
		/* @var $invoice Mage_Sales_Model_Service_Order */
		$invoice = Mage::getModel('sales/service_order', $this->_order)->prepareInvoice($items);
		$invoice->setEmailSent(true)->register();

		Mage::register('current_invoice', $invoice);
		return $invoice;
	}

	/**
	 * Initializes new One Page payment
	 */
	public function newAction() {
		$this->setSession();
		$this->setOrder();
		$this->forceNewOrderStatus();
		$this->setPayment(true);
		$this->_allShippingRates = Mage::getSingleton('checkout/type_onepage')->getQuote()->getBillingAddress()->getAllShippingRates();
		$this->getResponse()->setBody($this->getLayout()->createBlock('payu_account/redirect')->setAllShippingMethods($this->_allShippingRates)->setOrder($this->_order)->toHtml());
	}

	/**
	 * Before PayU summary action
	 */
	public function beforeSummaryAction(){
		$this->setSession();
		$this->setOrder();
		$this->setPayment(true);
		$this->getResponse()->setBody($this->getLayout()->createBlock('payu_account/beforeSummary')->setOrder($this->_order)->toHtml());
	}

	/**
	 * Processes PayU OrderNotifyRequest
	 */
	public function orderNotifyRequestAction(){

		try {
			Mage::getModel('payu_account/payment')->orderNotifyRequest();
		} catch (Exception $e) {
			Mage::logException($e);
		}


	}

	/**
	 * Shipping cost retrieve
	 *
	 * @todo not functioning yet
	 */
	public function shippingCostRetrieveAction(){
		$this->setSession();
		$this->setOrder();
		try {
			Mage::getModel('payu_account/payment')->shippingCostRetrieve();
		} catch (Exception $e) {
			Mage::logException($e);
		}

	}

	/**
	 * Complete payment action
	 */
	public function completePaymentAction(){
		 
		$this->setSession();
		$this->setOrder();
		$this->setPayment();
		//if(!Mage::getModel('payu/config')->getIsSelfReturnEnabled())
		//	Mage::getModel('payu/payment')->completePayment();
		if (defined('Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW')) {
			$this->_order->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true)->save();
		} else {
			$this->_order->setState(Mage_Sales_Model_Order::STATE_PROCESSING, true)->save();
		}
		Mage::getSingleton('checkout/session')->getQuote()->setIsActive(false)->save();
		Mage::getSingleton('checkout/session')->setSuccess( Mage::helper('payu_account')->__('Thank you.') );
		$this->_redirect('checkout/onepage/success',array('_secure' => true));

	}

	/**
	 * Cancel payment action
	 *
	 * @todo not yet implemented
	 */
	public function cancelPaymentAction(){
		Mage::getSingleton('checkout/session')->setMessage( Mage::helper('payu_account')->__('The payment has been cancelled.') );
		$this->_redirect('checkout/cart', array('_secure'=>true));
	}



	/**
	 * Error payment action
	 *
	 * @todo not yet implemented
	 */
	public function errorAction() {
		$this->_redirect('checkout/onepage/failure', array('_secure'=>true));
	}

	/** Setting checkout session */
	private function setSession() {
		$this->_session = Mage::getSingleton('checkout/session');
	}

	/** Setting the order */
	private function setOrder() {
		$id = $this->_session->getLastRealOrderId();
		$this->_order = Mage::getModel('sales/order')->loadByIncrementId($id);
	}

	/** Setting the new order */
	private function setNewOrder() {
		$this->_order = Mage::getModel('payu_account/payment')->prepareNewOrderByCart();
	}

	/**
	 * Setting the payment
	 *
	 * @param boolean
	 */
	private function setPayment($is_order_new = false) {
		$this->_payment = $this->_order->getPayment();
	}

	/**
	 * Check if the order is new
	 *
	 * @return boolean
	 */
	private function isNewOrder() {
		return (Mage::getSingleton('checkout/session')->getLastRealOrderId() == $this->_order->getRealOrderId());
	}


	/** Forcing the order to be new */
	private function forceNewOrderStatus() {
		if ($this->isNewOrder()) {
			$status = $this->_order->getStatus();
			$state = $this->_order->getState();
			if ($state == Mage_Sales_Model_Order::STATE_NEW && $status != Mage::getStoreConfig("payment/payu_account/order_status")) {
                $this->_order->setState(Mage::getStoreConfig("payment/payu_account/order_status"), true)
                    ->save();
            }
        }
    }  
	
}