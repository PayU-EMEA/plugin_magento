<?php

/**
 * PayU Payment Controller
 *
 * @copyright Copyright (c) PayU
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
     * Get Payu Model
     */
    private function getPayuModel($method)
    {
        return Mage::getModel('payu/method_' . lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $method)))));
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

            $redirectData = $this->getPayuModel($order->getPayment()->getMethod())->orderCreateRequest($order);

            $this->_redirectUrl($redirectData['redirectUri']);

            return;

        } catch (Exception $e) {
            Mage::logException($e);
            Mage::getSingleton('core/session')->addError($e->getMessage());
        }

        $this->_redirectAction('failure');
    }

    /**
     * Processes PayU OrderNotifyRequest
     */
    public function orderNotifyRequestAction()
    {
        try {
            $this->getPayuModel($this->getRequest()->getParam('method'))->orderNotifyRequest();
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

        $this->_redirectAction($this->getRequest()->getParam('error') ? 'failure' : 'success');

    }

    /**
     * @param string $action
     */
    private function _redirectAction($action)
    {
        $this->_redirect('checkout/onepage/' . $action, array('_secure' => true));
    }
}
