<?php

/**
 *    ver. 1.9.0
 *    PayU Standard Payment Controller
 *
 * @copyright  Copyright (c) 2011-2014 PayU
 * @license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *    http://www.payu.com
 *    http://www.openpayu.com
 *    http://twitter.com/openpayu
 */
class PayU_Account_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * @var Mage_Sales_Model_Order
     */
    protected $_order = null;

    /**
     * @return Mage_Checkout_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * @return PayU_Account_Model_Payment
     */
    public function getPayment()
    {
        return Mage::getModel('payu_account/payment');
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        if (is_null($this->_order)) {
            $this->_order = Mage::getModel('sales/order')
                ->loadByIncrementId($this->getSession()->getLastRealOrderId());
        }
        return $this->_order;
    }

    /**
     * Check if the order is new
     *
     * @return boolean
     */
    private function _isNewOrder()
    {
        return ($this->getSession()->getLastRealOrderId() == $this->getOrder()->getRealOrderId());
    }


    /** Forcing the order to be new */
    protected function _forceNewOrderStatus()
    {
        if ($this->_isNewOrder()) {
            $status       = $this->getOrder()->getStatus();
            $state        = $this->getOrder()->getState();
            $configStatus = Mage::getStoreConfig("payment/payu_account/order_status");
            if ($configStatus && $state == Mage_Sales_Model_Order::STATE_NEW && $status != $configStatus) {
                $this->getOrder()
                    ->setState($configStatus, true)
                    ->save();
            }
        }
    }

    /**
     * Initializes new One Step Checkout
     */
    public function newOneStepAction()
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $this->getPayment()->newOneStep();
        $this->getSession()->setLastRealOrderId($order->getIncrementId());
        $this->_redirect('*/*/new');
    }

    /**
     * Initializes new One Page payment
     */
    public function newAction()
    {
        try {
            $this->_forceNewOrderStatus();
            $allShippingRates = Mage::getSingleton('checkout/type_onepage')
                ->getQuote()
                ->getShippingAddress()
                ->getAllShippingRates();

            $redirectData = $this->getPayment()->orderCreateRequest($this->getOrder(), $allShippingRates);
            if (isset($redirectData['redirectUri'])) {
                $this->_redirectUrl($redirectData['redirectUri']);
                return;
            }
            $this->getSession()->addError(
                $this->__('There was a problem with the payment initialization, please contact system administrator.')
            );
        } catch (Mage_Core_Exception $e) {
            $this->getSession()->addError($e->getMessage());
        } catch (Exception $e) {
            $this->getSession()->addException($e,
                $this->__('There was a problem with the payment initialization, please contact system administrator.')
            );
        }
        $this->_redirect('*/*/error');
    }

    /**
     * Processes PayU OrderNotifyRequest
     */
    public function orderNotifyRequestAction()
    {
        try {
            $this->getPayment()->orderNotifyRequest();
        } catch (Exception $e) {
            Mage::logException($e);
        }
    }

    /**
     * Continue payment action
     */
    public function continuePaymentAction()
    {
        try {
            $this->getSession()->getQuote()->setIsActive(false)->save();
        } catch (Exception $e) {
            Mage::logException($e);
        }

        if (isset($_GET['error'])) {
            $this->_redirect('checkout/onepage/failure', array('_secure' => true));
        } else {
            $this->_redirect('checkout/onepage/success', array('_secure' => true));
        }
    }

    /**
     * Cancel payment action
     */
    public function cancelPaymentAction()
    {
        $this->getSession()->setMessage($this->__('The payment has been cancelled.'));
        $this->_redirect('checkout/cart', array('_secure' => true));
    }

    /**
     * Error payment action
     */
    public function errorAction()
    {
        $this->_redirect('checkout/onepage/failure', array('_secure' => true));
    }
}
