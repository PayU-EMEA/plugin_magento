<?php

/**
 *    ver. 1.9.0
 *    PayU Adminhtml Sales Order Controller
 *
 * @copyright  Copyright (c) 2011-2014 PayU
 * @license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *    http://www.payu.com
 *    http://www.openpayu.com
 *    http://twitter.com/openpayu
 */

require_once Mage::getModuleDir('controllers', 'Mage_Adminhtml') . '/Sales/OrderController.php';

class PayU_Account_Adminhtml_Sales_OrderController extends Mage_Adminhtml_Sales_OrderController
{
    /**
     * @return PayU_Account_Model_Payment
     */
    public function getPayment()
    {
        return Mage::getSingleton('payu_account/payment');
    }

    /**
     * Cancel
     */
    public function cancelPayUOrderAction()
    {
        $order = $this->_initOrder();
        try {
            if ($this->getPayment()->cancelOrder($order)) {
                $message = $this->__('The order is awaiting for cancelation in PayU, please wait while status changes.');
                $this->_getSession()->addSuccess($message);
                $order->addStatusHistoryComment($message)->save();
            } else {
                $this->_getSession()->addError($this->__('There was a problem while canceling the order in PayU.'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     * Reject
     */
    public function rejectPayUOrderAction()
    {
        $order = $this->_initOrder();
        try {
            if ($this->getPayment()->rejectOrder($order)) {
                $message = $this->__('The order has been rejected in PayU.');
                $this->_getSession()->addSuccess($message);
                $order->addStatusHistoryComment($message)->save();
            } else {
                $this->_getSession()->addError($this->__('There was a problem while rejecting the order in PayU.'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }

    /**
     *
     */
    public function completePayUOrderAction()
    {
        $order = $this->_initOrder();
        try {
            if ($this->getPayment()->completeOrder($order)) {
                $message = $this->__('The order is completing in PayU, please wait while status changes.');
                $this->_getSession()->addSuccess($message);
                $order->addStatusHistoryComment($message)->save();
            } else {
                $this->_getSession()->addError($this->__('There was a problem while completing the order in PayU.'));
            }
        } catch (Exception $e) {
            $this->_getSession()->addException($e, $e->getMessage());
        }
        $this->_redirect('*/sales_order/view', array('order_id' => $order->getId()));
    }
}
