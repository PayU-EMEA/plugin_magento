<?php

/**
 * PayU Standard Payment Controller
 *
 * @copyright Copyright (c) 2011-2016 PayU
 * @license http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 */
class PayU_Account_PaymentController extends Mage_Core_Controller_Front_Action
{
    /**
     * Get singleton of Checkout Session Model
     *
     * @return Mage_Checkout_Model_Session
     */
    protected function _getCheckout()
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
     * Initializes new One Page payment
     */
    public function newAction()
    {
        try {
            $session = $this->_getCheckout();

            $order = Mage::getModel('sales/order');
            $order->loadByIncrementId($session->getLastRealOrderId());

            if (!$order->getId()) {
                Mage::throwException($this->__('No order for processing found'));
            }

            $redirectData = $this->getPayment()->orderCreateRequest($order);

            $this->_redirectUrl($redirectData['redirectUri']);

            return;

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());

        }

        $this->_errorAction();
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
            $this->_getCheckout()->getQuote()->setIsActive(false)->save();
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
     * Error payment action
     */
    public function _errorAction()
    {
        $this->_redirect('checkout/onepage/failure', array('_secure' => true));
    }
}
