<?php

/**
 * PayU -Standard Payment Model
 *
 * @copyright Copyright (c) 2011-2016 PayU
 * @license http://opensource.org/licenses/GPL-3.0 Open Software License (GPL 3.0)
 * http://www.payu.com
 */

require_once(Mage::getBaseDir('lib') . '/PayU/openpayu.php');


abstract class PayU_Account_Model_Method_Abstract extends Mage_Payment_Model_Method_Abstract
{
    const DELIMITER = '-';

    /**
     * Module identifiers
     */
    protected $_code = 'payu_abstract';

    /**
     * Transaction id
     */
    protected $_transactionId;

    /**
     * Currently processed order
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;

    protected $_isGateway = false;
    protected $_canOrder = false;
    protected $_canAuthorize = false;
    protected $_canCapture = true;
    protected $_canCapturePartial = false;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = false;
    protected $_canUseInternal = true;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canReviewPayment = true;
    protected $_isInitializeNeeded = true;

    protected $_payUOrderResult = null;

    /** @var PayU_Account_Model_Config  */
    protected $_payuConfig;

    public function __construct()
    {
        parent::__construct();
        $this->_payuConfig = Mage::getSingleton('payu/config', array('method' => $this->_code));
        $this->_initializeOpenPayUConfiguration();
    }

    /**
     * @return PayU_Account_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('payu');
    }

    /**
     * @param string $extOrderId
     */
    protected function _setOrderByOrderId($extOrderId)
    {
        $this->_order = Mage::getModel('sales/order')->load($extOrderId);
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @param Mage_Sales_Model_Order $order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
    }

    /**
     * Redirection url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('payu/payment/new', array('_secure' => true));
    }

    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote()
    {
        return $this->getCheckoutSession()->getQuote();
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckoutSession()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Create order
     *
     * @param Mage_Sales_Model_Order
     * @return array
     */
    public function orderCreateRequest(Mage_Sales_Model_Order $order)
    {
        $this->setOrder($order);
        $response = array();
        $items = array();
        $items[] = array(
            'quantity' => 1,
            'name' => $this->_helper()->__('Shipping') . ': ' . $order->getShippingDescription(),
            'unitPrice' => $this->_toAmount($order->getShippingInclTax())
        );

        $orderItems = $order->getAllVisibleItems();

        foreach ($orderItems as $key => $item) {
            $itemInfo = $item->getData();

            $items[] = array(
                'quantity' => (int)$itemInfo['qty_ordered'],
                'name' => $itemInfo['name'],
                'unitPrice' => $this->_toAmount($itemInfo['price_incl_tax'])
            );
        }

        $OCReq = array(
            'merchantPosId' => $this->_payuConfig->getMerchantPosId(),
            'description' => $this->_helper()->__('Order #%s', $order->getRealOrderId()),
            'products' => $items,
            'customerIp' => trim(strtok(Mage::app()->getFrontController()->getRequest()->getClientIp(), ',')),
            'notifyUrl' => $this->_payuConfig->getUrl('orderNotifyRequest', array('method' => $this->_code)),
            'continueUrl' => $this->_payuConfig->getUrl('continuePayment'),
            'currencyCode' => $order->getOrderCurrencyCode(),
            'totalAmount' => $this->_toAmount($order->getGrandTotal()),
            'extOrderId' => uniqid($order->getId() . self::DELIMITER, true),
            'settings' => array(
                'invoiceDisabled' => true
            )
        );

        $billingAddressId = $order->getBillingAddressId();

        if (!empty ($billingAddressId)) {
            $billingAddress = $order->getBillingAddress();
            $customerEmail = $billingAddress->getEmail();
            if (!empty ($customerEmail)) {
                $customerSheet = array(
                    'email' => $billingAddress->getEmail(),
                    'phone' => $billingAddress->getTelephone(),
                    'firstName' => $billingAddress->getFirstname(),
                    'lastName' => $billingAddress->getLastname(),
                    'language' => $this->_getLanguageCode()
                );

                if (!$order->getIsVirtual() && !empty($order->getShippingAddressId())) {
                    $shippingAddress = $order->getShippingAddress();

                    $customerSheet ['delivery'] = array(
                        'street' => trim(implode(' ', $shippingAddress->getStreet())),
                        'postalCode' => $shippingAddress->getPostcode(),
                        'city' => $shippingAddress->getCity(),
                        'countryCode' => $shippingAddress->getCountry(),
                        'recipientName' => trim(
                            $shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname()
                        ),
                        'recipientPhone' => $shippingAddress->getTelephone(),
                        'recipientEmail' => $shippingAddress->getEmail()
                    );
                }
                $OCReq['buyer'] = $customerSheet;
            }
        }

        if ($this->_code === 'payu_card') {
            $OCReq['payMethods'] = array(
                'payMethod' => array(
                    'type' => 'PBL',
                    'value' => 'c'
                )
            );
        }

        try {
            $requestObject = new Varien_Object($OCReq);
            $eventArgs = array('request' => $requestObject);
            Mage::dispatchEvent('payu_order_create_request', $eventArgs);
            $OCReq = $requestObject->getData();
            
            $result = OpenPayU_Order::create($OCReq);

            if ($result->getStatus() == OpenPayU_Order::SUCCESS) {
                $this->_transactionId = $result->getResponse()->orderId;

                Mage::getSingleton('core/session')->setPayUSessionId($this->_transactionId);

                $payment = $order->getPayment();
                
                $eventArgs = array(
                    'result' => $result,
                    'order' => $order,
                    'payment' => $payment,
                );
                Mage::dispatchEvent('payu_order_create_success', $eventArgs);

                $payment->setAdditionalInformation('payu_payment_status', OpenPayuOrderStatus::STATUS_NEW)
                    ->setAdditionalInformation('req_ext_order_id', $OCReq['extOrderId'])
                    ->save();

                $this->_updatePaymentStatusNew($payment);

                $response = array(
                    'redirectUri' => $result->getResponse()->redirectUri
                );

            } else {
                Mage::throwException($this->_helper()
                    ->__('There was a problem with the payment initialization, please contact system administrator.'));
            }
        } catch (Exception $e) {
            Mage::throwException($this->_helper()
                ->__('There was a problem with the payment initialization - "%s", please contact system administrator.', $e->getMessage()));

            Mage::logException($e);
        }

        $order->sendNewOrderEmail()->save();
        return $response;
    }


    /**
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);
        $sessionId = $payment->getLastTransId();

        if (empty($sessionId)) {
            return false;
        }

        if (!$this->_orderStatusUpdateRequest(OpenPayuOrderStatus::STATUS_COMPLETED, $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    public function denyPayment(Mage_Payment_Model_Info $payment)
    {
        parent::denyPayment($payment);
        $sessionId = $payment->getLastTransId();

        if (empty($sessionId)) {
            return false;
        }

        if (!$this->_orderStatusUpdateRequest(OpenPayuOrderStatus::STATUS_CANCELED, $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     * Refund payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount)
    {
        /** @var Mage_Sales_Model_Order $order */
        $order = $payment->getOrder();

        try {
            $result = OpenPayU_Refund::create($order->getPayment()->getLastTransId(), $this->_helper()->__('Refund for order: %s', $order->getIncrementId()), $this->_toAmount($amount));

            $comment = $this->_helper()->__('Payu refund - amount: %s, status: %s', $amount, $result->getStatus());

            $order->addStatusHistoryComment($comment)
                ->save();

            if ($result->getStatus() == OpenPayU_Order::SUCCESS) {
                return $this;
            }

        } catch (OpenPayU_Exception $e) {
            $comment = $this->_helper()->__('Payu refund - amount: %s, status: %s', $amount, $e->getMessage());

            Mage::throwException($comment);
        }

    }

    public function orderNotifyRequest()
    {
        $body = file_get_contents('php://input');
        $data = trim($body);

        try {
            $result = OpenPayU_Order::consumeNotification($data);
        } catch (Exception $e) {
            header('X-PHP-Response-Code: 500', true, 500);
            die($e->getMessage());
        }
        $response = $result->getResponse();
        $orderRetrieved = $response->order;

        if (isset($orderRetrieved) && is_object($orderRetrieved) && $orderRetrieved->orderId) {
            $this->_transactionId = $orderRetrieved->orderId;
            $extOrderIdExploded = explode(self::DELIMITER, $orderRetrieved->extOrderId);
            $orderId = array_shift($extOrderIdExploded);

            $this->_setOrderByOrderId($orderId);
            $this->_updatePaymentStatus($orderRetrieved->status);

            header("HTTP/1.1 200 OK");
        }
        exit;
    }

    /**
     * Update payment status
     *
     * @param $paymentStatus
     */
    protected function _updatePaymentStatus($paymentStatus)
    {
        $payment = $this->getOrder()->getPayment();

        $currentState = $payment->getAdditionalInformation('payu_payment_status');

        if ($currentState != OpenPayuOrderStatus::STATUS_COMPLETED && $currentState != $paymentStatus) {
            try {
                switch ($paymentStatus) {

                    case OpenPayuOrderStatus::STATUS_PENDING:
                        //nothing to do
                        break;

                    case OpenPayuOrderStatus::STATUS_CANCELED:
                        $this->_updatePaymentStatusCanceled($payment);
                        break;

                    case OpenPayuOrderStatus::STATUS_WAITING_FOR_CONFIRMATION:
                    case OpenPayuOrderStatus::STATUS_REJECTED:
                        $this->_updatePaymentStatusRejected($payment);
                        break;

                    case OpenPayuOrderStatus::STATUS_COMPLETED:
                        $this->_updatePaymentStatusCompleted($payment);
                        break;
                }

                $payment->setAdditionalInformation('payu_payment_status', $paymentStatus)
                    ->save();

            } catch (Exception $e) {
                Mage::logException($e);
            }
        }
    }

    /**
     * Update payment status to new
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    protected function _updatePaymentStatusNew(Mage_Sales_Model_Order_Payment $payment)
    {
        $comment = $this->_helper()->__('New transaction started.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setIsTransactionApproved(false)
            ->setIsTransactionClosed(false)
            ->save();

        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, $comment)
            ->save();

        $payment->getOrder()
            ->save();

    }

    /**
     * Change the status to canceled
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    protected function _updatePaymentStatusCanceled(Mage_Sales_Model_Order_Payment $payment)
    {
        $comment = $this->_helper()->__('The transaction has been canceled.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setIsTransactionApproved(true)
            ->setIsTransactionClosed(true)
            ->save();

        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER, null, false, $comment)
            ->save();

        $payment->getOrder()
            ->sendOrderUpdateEmail(true, $comment)
            ->cancel()
            ->save();

    }

    /**
     * Change the status to rejected
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    protected function _updatePaymentStatusRejected(Mage_Sales_Model_Order_Payment $payment)
    {
        $comment = $this->_helper()->__('The transaction is to be accepted or rejected.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->save();

        $payment->getOrder()
            ->setState(Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, $comment)
            ->save();
    }

    /**
     * Update payment status to complete
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    protected function _updatePaymentStatusCompleted(Mage_Sales_Model_Order_Payment $payment)
    {

        $comment = $this->_helper()->__('The transaction completed successfully.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setCurrencyCode($payment->getOrder()->getBaseCurrencyCode())
            ->setIsTransactionApproved(true)
            ->setIsTransactionClosed(true)
            ->registerCaptureNotification($this->getOrder()->getTotalDue(), true)
            ->save();

        $this->getOrder()
            ->save();

        if ($invoice = $payment->getCreatedInvoice()) {
            $comment = $this->_helper()->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
            $this->getOrder()
                ->queueNewOrderEmail()
                ->addStatusHistoryComment($comment)
                ->setIsCustomerNotified(true)
                ->save();
        }

    }

    /**
     * @param  $status
     * @param  $sessionId
     * @return bool OpenPayU_Result
     */
    protected function _orderStatusUpdateRequest($status, $sessionId)
    {
        if (empty($sessionId)) {
            $sessionId = $this->getOrder()->getPayment()->getLastTransId();
        }

        if (empty($sessionId)) {
            Mage::log("PayU sessionId empty: " . $this->getId());
            return false;
        }

        if ($status == OpenPayuOrderStatus::STATUS_CANCELED) {
            $result = OpenPayU_Order::cancel($sessionId);
        } elseif ($status == OpenPayuOrderStatus::STATUS_COMPLETED) {
            $status_update = array(
                "orderId" => $sessionId,
                "orderStatus" => OpenPayuOrderStatus::STATUS_COMPLETED
            );
            $result = OpenPayU_Order::statusUpdate($status_update);
        } else {
            return false;
        }

        if ($result->getStatus() == OpenPayU_Order::SUCCESS) {
            return true;
        } else {
            Mage::log("PayU error while updating status: " . $result->getError());
            return false;
        }
    }

    /**
     * Returns amount in PayU acceptable format
     *
     * @param $val
     * @return int
     */
    protected function _toAmount($val)
    {
        return $this->_helper()->toAmount($val);
    }

    /**
     * Returns current language code
     *
     * @return string
     */
    protected function _getLanguageCode()
    {
        $locale = Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId());
        $langCode = explode('_', $locale, 2);
        return strtolower($langCode[0]);
    }

    /**
     * Initialize PayU configuration
     */
    protected function _initializeOpenPayUConfiguration()
    {
        OpenPayU_Configuration::setEnvironment($this->_payuConfig->isSandbox() ? 'sandbox' : 'secure');
        OpenPayU_Configuration::setMerchantPosId($this->_payuConfig->getMerchantPosId());
        OpenPayU_Configuration::setSignatureKey($this->_payuConfig->getSignatureKey());
        if ($this->_payuConfig->getClientId() && $this->_payuConfig->getClientSecret()) {
            OpenPayU_Configuration::setOauthClientId($this->_payuConfig->getClientId());
            OpenPayU_Configuration::setOauthClientSecret($this->_payuConfig->getClientSecret());
        }
        OpenPayU_Configuration::setOauthTokenCache(new OauthCacheFile(Mage::getBaseDir('cache')));
        OpenPayU_Configuration::setSender('Magento ver ' . Mage::getVersion() . '/Plugin ver ' . $this->_payuConfig->getPluginVersion());
    }

}
