<?php

/**
 * ver. 1.9.0
 * PayU -Standard Payment Model
 *
 * @copyright Copyright (c) 2011-2014 PayU
 * @license http://opensource.org/licenses/GPL-3.0 Open Software License (GPL 3.0)
 *          http://www.payu.com
 *          http://www.openpayu.com
 *          http://twitter.com/openpayu
 */
class PayU_Account_Model_Payment extends Mage_Payment_Model_Method_Abstract
{
    const DELIMITER = '-';
    /**
     * The base module url
     *
     * @var string
     */
    protected $_myUrl;

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = 'payu_account';

    /**
     * Block type
     *
     * @var string
     */
    protected $_formBlockType = 'payu_account/form';

    /**
     * Is initialization needed
     *
     * @var boolean
     */
    protected $_isInitializeNeeded = true;

    /**
     * Transaction id
     */
    protected $_transactionId;

    /**
     * Pay method
     */
    protected $_payuPayMethod;

    /**
     * Currently processed order
     *
     * @var Mage_Sales_Model_Order
     */
    protected $_order;
    protected $_tempInfo                = "AWAITING_PayU";
    protected $_isGateway               = true;
    protected $_canOrder                = true;
    protected $_canAuthorize            = true;
    protected $_canCapture              = true;
    protected $_canCapturePartial       = true;
    protected $_canRefund               = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid                 = false;
    protected $_canUseInternal          = false;
    protected $_canUseCheckout          = true;
    protected $_canUseForMultishipping  = false;
    protected $_canSaveCc               = false;
    protected $_canReviewPayment        = true;
    protected $_payUOrderResult         = null;

    public function __construct()
    {
        parent::__construct();
        $this->initializeOpenPayUConfiguration();
    }

    /**
     * @return PayU_Account_Helper_Data
     */
    protected function _helper()
    {
        return Mage::helper('payu_account');
    }

    /**
     * Initialize PayU configuration
     */
    protected function initializeOpenPayUConfiguration()
    {
        OpenPayU_Configuration::setApiVersion(2.1);
        OpenPayU_Configuration::setEnvironment($this->getConfig()->getEnvironment());
        OpenPayU_Configuration::setMerchantPosId($this->getConfig()->getMerchantPosId());
        OpenPayU_Configuration::setSignatureKey($this->getConfig()->getSignatureKey());
        OpenPayU_Configuration::setSender("Magento ver " . Mage::getVersion() . "/Plugin ver " . $this->getConfig()->getPluginVersion());
    }

    /**
     * Get PayU -configuration
     *
     * @return PayU_Account_Model_Config
     */
    public function getConfig()
    {
        return Mage::getSingleton('payu_account/config');
    }

    /**
     * @param int $extOrderId
     * @return $this
     */
    public function setOrderByOrderId($extOrderId)
    {
        $this->_order = Mage::getModel('sales/order')->load($extOrderId);
        return $this;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function getOrder()
    {
        return $this->_order;
    }

    /**
     * @return Mage_Sales_Model_Order
     */
    public function setOrder(Mage_Sales_Model_Order $order)
    {
        $this->_order = $order;
        return $this;
    }

    /**
     * Get PayU session namespace
     *
     * @return PayU_Account_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('payu_account/session');
    }

    /**
     * Redirection url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl()
    {
        return Mage::getUrl('payu_account/payment/new', array('_secure' => true));
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
     * Initializes the payment.
     * @param Mage_Sales_Model_Order
     * @param Mage_Shipping_Model_Shipping
     * @return array
     */
    public function orderCreateRequest(Mage_Sales_Model_Order $order, $allShippingRates)
    {
        $this->setOrder($order);
        $orderCurrencyCode = $order->getOrderCurrencyCode();
        $orderCountryCode  = $order->getBillingAddress()->getCountry();
        $shippingCostList  = array();
        if (empty ($allShippingRates) || Mage::getSingleton('customer/session')->isLoggedIn()) {

            if ($order->getShippingInclTax() > 0) {
                $shippingCostList ['shippingMethods'] [] = array(
                    'name'    => $order->getShippingDescription(),
                    'country' => $orderCountryCode,
                    'price'   => $this->_toAmount($order->getShippingInclTax()),
                );
            }

            $grandTotal = $order->getGrandTotal() - $order->getShippingInclTax();
        } else {
            $firstPrice = 0;
            foreach ($allShippingRates as $key => $rate) {
                $gross = $this->_toAmount($rate->getPrice());
                if ($key == 0) {
                    $firstPrice = $rate->getPrice();
                }

                $shippingCostList ['shippingMethods'] [] = array(
                    'name'    => $rate->getMethodTitle(),
                    'country' => $orderCountryCode,
                    'price'   => $gross
                );

            }
            $grandTotal = $order->getGrandTotal() - $firstPrice;
        }

        $orderItems    = $order->getAllVisibleItems();
        $items         = array();
        $productsTotal = 0;
        $isDiscount    = false;
        $response      = array( // default redirect to failure action
            'redirectUri' => Mage::getUrl('checkout/onepage/failure', array('_secure' => true)),
        );

        foreach ($orderItems as $key => $item) {
            $itemInfo = $item->getData();
            if ($itemInfo['discount_amount'] > 0) {
                $itemInfo['price_incl_tax'] = $itemInfo['price_incl_tax'] - $itemInfo['discount_amount'];
                $isDiscount                 = true;
            } else {
                if ($itemInfo['discount_percent'] > 0) {
                    $itemInfo['price_incl_tax'] = $itemInfo['price_incl_tax'] * (100 - $itemInfo['discount_percent']) / 100;
                }
            }

            // Check if the item is countable one
            if ($this->_toAmount($itemInfo['price_incl_tax']) > 0) {
                $items['products'][] = array(
                    'quantity'  => (int)$itemInfo['qty_ordered'],
                    'name'      => $itemInfo['name'],
                    'unitPrice' => $this->_toAmount($itemInfo['price_incl_tax'])
                );
                $productsTotal += $itemInfo['price_incl_tax'] * $itemInfo['qty_ordered'];
            }
        }

        // assigning the shopping cart
        $shoppingCart = array(
            'grandTotal'        => $this->_toAmount($grandTotal),
            'CurrencyCode'      => $orderCurrencyCode,
            'ShoppingCartItems' => $items
        );

        $orderInfo = array(
            'merchantPosId' => OpenPayU_Configuration::getMerchantPosId(),
            'orderUrl'      => Mage::getUrl('sales/order/view', array('order_id' => $order->getId())),
            'description'   => 'Order no ' . $order->getRealOrderId(),
            'validityTime'  => $this->getConfig()->getOrderValidityTime()
        );

        if ($isDiscount) {
            $items['products']   = array();
            $items['products'][] = array(
                'quantity'  => 1,
                'name'      => $this->_helper()->__('Order #%s', $order->getId()),
                'unitPrice' => $this->_toAmount($grandTotal)
            );
        }

        $OCReq                  = $orderInfo;
        $OCReq ['products']     = $items['products'];
        $OCReq ['customerIp']   = Mage::app()->getFrontController()->getRequest()->getClientIp();
        $OCReq ['notifyUrl']    = $this->getConfig()->getUrl('orderNotifyRequest');
        $OCReq ['cancelUrl']    = $this->getConfig()->getUrl('cancelPayment');
        $OCReq ['continueUrl']  = $this->getConfig()->getUrl('continuePayment');
        $OCReq ['currencyCode'] = $orderCurrencyCode;
        $OCReq ['totalAmount']  = $shoppingCart ['grandTotal'];
        $OCReq ['extOrderId']   = $order->getId() . self::DELIMITER . microtime();

        if (!empty($shippingCostList)) {
            $OCReq ['shippingMethods'] = $shippingCostList['shippingMethods'];
        }
        unset ($OCReq ['shoppingCart']);

        $billingAddressId = $order->getBillingAddressId();

        if (!empty ($billingAddressId)) {
            $billingAddress = $order->getBillingAddress();
            $customerEmail  = $billingAddress->getEmail();
            if (!empty ($customerEmail)) {
                $customerSheet     = array(
                    'email'     => $billingAddress->getEmail(),
                    'phone'     => $billingAddress->getTelephone(),
                    'firstName' => $billingAddress->getFirstname(),
                    'lastName'  => $billingAddress->getLastname()
                );
                $shippingAddressId = $order->getShippingAddressId();

                if (!empty ($shippingAddressId)) {
                    $shippingAddress = $order->getShippingAddress();
                }

                if (!$order->getIsVirtual()) {
                    $customerSheet ['delivery'] = array(
                        'street'         => trim(implode(' ', $shippingAddress->getStreet())),
                        'postalCode'     => $shippingAddress->getPostcode(),
                        'city'           => $shippingAddress->getCity(),
                        'countryCode'    => $shippingAddress->getCountry(),
                        'recipientName'  => trim($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname()),
                        'recipientPhone' => $shippingAddress->getTelephone(),
                        'recipientEmail' => $shippingAddress->getEmail()
                    );
                }
                $OCReq ['buyer'] = $customerSheet;
            }
        }
        try {
            $result = OpenPayU_Order::create($OCReq);
            if ($result->getStatus() == OpenPayU_Order::STATUS_SUCCESS) {
                // store session identifier in session info
                Mage::getSingleton('core/session')->setPayUSessionId($result->getResponse()->orderId);
                // assign current transaction id
                $this->_transactionId = $result->getResponse()->orderId;
                $order->getPayment()->setLastTransId($this->_transactionId);
                $locale   = Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId());
                $langCode = explode('_', $locale, 2);
                $response = array(
                    'redirectUri' => $result->getResponse()->redirectUri,
                    'url'         => OpenPayu_Configuration::getSummaryUrl(),
                    'sessionId'   => $result->getResponse()->orderId,
                    'lang'        => strtolower($langCode [1])
                );

                $customer = Mage::getModel('customer/customer');

                if ($order->getCustomerIsGuest()) {
                    $email = $billingAddress->getEmail();
                    $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                    $customer->loadByEmail($email);

                    if (!$customer->getId()) {
                        $order->setCustomerEmail($email);
                    }
                } else {
                    $customer->load($order->getCustomerId());
                }

            } else {
                Mage::throwException($this->_helper()
                    ->__('There was a problem with the payment initialization, please contact system administrator.'));
            }
        } catch (Exception $e) {
            Mage::logException($e);
        }

        $order->sendNewOrderEmail();
        $order->save();
        return $response;
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
     * Refund payment
     *
     * @param Varien_Object $payment
     * @param float         $amount
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount)
    {
        $order  = $payment->getOrder();
        $amount = $amount * 100;
        $result = OpenPayU_Refund::create($order->getPayment()->getLastTransId(), 'Magento payu refund', $amount);

        if ($result->getStatus() == OpenPayU_Order::STATUS_SUCCESS) {
            return $this;
        }

        $serverMsg = OpenPayU_Util::statusDesc($result->getStatus());
        $errorMsg  = $this->_getHelper()->__($serverMsg);
        Mage::throwException($errorMsg);
    }

    /**
     * One Step Checkout initialization
     *
     * @return Mage_Sales_Model_Order
     */
    public function newOneStep()
    {
        $checkout        = Mage::getSingleton('checkout/type_onepage');
        $customerSession = Mage::getSingleton('customer/session');
        $checkout->initCheckout();

        // if guest payment
        if (!$customerSession->isLoggedIn()) {

            $billingAddress = array(
                'address_id'           => 5,
                'firstname'            => $this->_tempInfo,
                'lastname'             => $this->_tempInfo,
                'company'              => "",
                'street'               => array(0 => $this->_tempInfo,
                                                1 => $this->_tempInfo),
                'region_id'            => '',
                'region'               => '',
                'city'                 => $this->_tempInfo,
                'postcode'             => $this->_tempInfo,
                'country_id'           => Mage::helper('core')->getDefaultCountry(),
                'telephone'            => "0000000",
                'save_in_address_book' => 0
            );

            $checkout->saveBilling($billingAddress, false);
            $checkout->saveShipping($billingAddress, false);
            $checkout->saveCheckoutMethod('guest');
            $checkout->getQuote()
                ->setCustomerId(null)
                ->setCustomerIsGuest(true)
                ->setCustomerEmail(md5(rand() . rand()) . "_TEMP_PayU@" . Mage::getModel('payu_account/config')->getDomainName())
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        } else {
            $billing  = $checkout->getQuote()->getBillingAddress();
            $shipping = $checkout->getQuote()->isVirtual() ? null : $checkout->getQuote()->getShippingAddress();

            $customerBilling = $billing->exportCustomerAddress();

            $billing->setCustomerAddress($customerBilling);
            $customerBilling->setIsDefaultBilling(true);

            if ($shipping && !$shipping->getSameAsBilling()) {
                $customerShipping = $shipping->exportCustomerAddress();
                $shipping->setCustomerAddress($customerShipping);
                $customerShipping->setIsDefaultShipping(true);
            } else {
                $customerBilling->setIsDefaultShipping(true);
            }

            $checkout->saveCheckoutMethod('register');

            $checkout->getQuote()->setCustomerId($customerSession->getCustomerId())->setCustomerIsGuest(false);
        }

        $checkout->getQuote()->getBillingAddress()
            ->setShippingMethod('flatrate_flatrate')->setCollectShippingRates(true)->save();
        $checkout->getQuote()->getShippingAddress()
            ->setShippingMethod('flatrate_flatrate')->setCollectShippingRates(true)->save();

        // presetting the default shipment method
        $checkout->saveShippingMethod('flatrate_flatrate');
        $checkout->getQuote()->collectTotals()->save();

        // assigning the payment method type
        $checkout->savePayment(array(
            'method' => $this->_code
        ));

        $checkout->saveOrder();
        $order = Mage::getModel('sales/order')->load($checkout->getQuote()->getId(), 'quote_id');

        $storeId                   = Mage::app()->getStore()->getId();
        $paymentHelper             = Mage::helper("payment");
        $zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice($storeId);
        if ($paymentHelper->isZeroSubTotal($storeId) && $order->getGrandTotal() == 0 &&
            $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE &&
            $paymentHelper->getZeroSubTotalOrderStatus($storeId) == 'pending'
        ) {
            $invoice = $this->_initInvoice();
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')->addObject($invoice)->addObject($invoice->getOrder());
            $transactionSave->save();
        }

        return $order;
    }

    /**
     * Create invoice
     *
     * @return Mage_Sales_Model_Order_Invoice
     */
    protected function _initInvoice()
    {
        $items = array();
        foreach ($this->getOrder()->getAllItems() as $item) {
            $items[$item->getId()] = $item->getQtyOrdered();
        }

        /* @var $invoice Mage_Sales_Model_Service_Order */
        $invoice = Mage::getModel('sales/service_order', $this->getOrder())->prepareInvoice($items);
        $invoice->setEmailSent(true)->register();

        Mage::register('current_invoice', $invoice);
        return $invoice;
    }

    /**
     *
     * @param Mage_Payment_Model_Info $payment
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment)
    {
        parent::acceptPayment($payment);
        $sessionId = $payment->getLastTransId();

        if (empty ($sessionId)) {
            return false;
        }

        if (!$this->_orderStatusUpdateRequest(OpenPayU_Order::STATUS_COMPLETED, $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     * @param  $status
     * @param  $sessionId
     * @return bool OpenPayU_Result
     */
    protected function _orderStatusUpdateRequest($status, $sessionId)
    {
        if (empty ($sessionId)) {
            $sessionId = $this->getOrder()->getPayment()->getLastTransId();
        }

        if (empty ($sessionId)) {
            Mage::log("PayU sessionId empty: " . $this->getId());
            return false;
        }

        $status_update = array(
            "orderId"     => stripslashes($sessionId),
            "orderStatus" => $status
        );

        $result = OpenPayU_Order::statusUpdate($status_update);

        if ($result) {
            return true;
        } else {
            Mage::log("PayU error while updating status: " . $result->getError());
        }
        return $result;
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

        if (empty ($sessionId)) {
            return false;
        }

        if (!$this->_orderStatusUpdateRequest(OpenPayU_Order::STATUS_REJECTED, $sessionId)) {
            return false;
        }

        return true;
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool OpenPayU_Result
     */
    public function completeOrder(Mage_Sales_Model_Order $order)
    {
        $result = $this->_orderStatusUpdateRequest(OpenPayU_Order::STATUS_COMPLETED,
            $order->getPayment()->getLastTransId());
        return $result;
    }

    /**
     *
     * @param $order
     * @return bool
     */
    public function rejectOrder(Mage_Sales_Model_Order $order)
    {
        return $this->cancelOrder($order);
    }

    /**
     *
     * @param Mage_Sales_Model_Order $order
     * @return bool
     */
    public function cancelOrder(Mage_Sales_Model_Order $order)
    {
        $result = OpenPayU_Order::cancel($order->getPayment()->getLastTransId());
        if ($result->getStatus() == OpenPayU_Order::STATUS_SUCCESS) {
            return true;
        }
        return false;
    }

    /**
     * Cancel payment
     */
    public function cancelPayment()
    {
        $sessionId = Mage::app()->getRequest()->getParam('sessionId');
        if (!empty ($sessionId)) {
            OpenPayU_Order::cancel($sessionId);
        }
    }

    public function orderNotifyRequest()
    {
        $body = file_get_contents('php://input');
        $data = trim($body);

        $result         = OpenPayU_Order::consumeNotification($data);
        $response       = $result->getResponse();
        $orderRetrieved = $response->order;
        if (isset($orderRetrieved) && is_object($orderRetrieved) && $orderRetrieved->orderId) {
            $this->_transactionId = $orderRetrieved->orderId;
            $extOrderIdExploded   = explode(self::DELIMITER, $orderRetrieved->extOrderId);
            $orderId              = array_shift($extOrderIdExploded);

            $this->setOrderByOrderId($orderId);
            if (isset($orderRetrieved->payMethod->type)) {
                $this->_payuPayMethod = $orderRetrieved->payMethod->type;
            }
            $payUPaymentStatus = $orderRetrieved->status;
            $this->_updatePaymentStatus($payUPaymentStatus);

            //the response should be status 200
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
        $payment      = $this->getOrder()->getPayment();
        $currentState = $payment->getAdditionalInformation('payu_payment_status');

        if ($currentState != OpenPayU_Order::STATUS_COMPLETED && $currentState != $paymentStatus) {
            try {
                switch ($paymentStatus) {
                    case OpenPayU_Order::STATUS_NEW:
                        $this->_updatePaymentStatusNew($payment);
                        break;

                    case OpenPayU_Order::STATUS_PENDING:
                        if ($currentState != OpenPayU_Order::STATUS_COMPLETED) {
                            $this->_updatePaymentStatusPending($payment);
                        }
                        break;

                    case OpenPayU_Order::STATUS_CANCELED:
                        $this->_updatePaymentStatusCanceled($payment);
                        break;

                    case OpenPayU_Order::STATUS_REJECTED:
                        $this->_updatePaymentStatusDenied($payment);
                        break;

                    case OpenPayU_Order::STATUS_COMPLETED:
                        $this->_updatePaymentStatusCompleted($payment);
                        break;
                }
                // set current PayU status information and save
                $payment->setAdditionalInformation('payu_payment_status', $paymentStatus)->save();
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
            ->setIsTransactionClosed(false)
            ->save();

        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER)
            ->save();

        $payment->getOrder()
            ->save();

        $payment->getOrder()->addStatusHistoryComment($comment)
            ->save();
    }

    /**
     * Update payment status to pending
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    protected function _updatePaymentStatusPending(Mage_Sales_Model_Order_Payment $payment)
    {
        $comment = $this->_helper()->__('The transaction is pending.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setIsTransactionApproved(false)
            ->setIsTransactionClosed(false)
            ->save();

        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER)
            ->save();

        $payment->getOrder()
            ->save();

        $payment->getOrder()->addStatusHistoryComment($comment)
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
            ->setIsTransactionApproved(false)
            ->setIsTransactionClosed(true)
            ->cancel()
            ->save();

        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER)
            ->save();


        $payment->getOrder()
            ->sendOrderUpdateEmail(true, $comment)
            ->save();

        $payment->getOrder()->addStatusHistoryComment($comment)
            ->save();
    }

    /**
     * Change the status to rejected
     *
     * @param Mage_Sales_Model_Order_Payment $payment
     */
    protected function _updatePaymentStatusDenied(Mage_Sales_Model_Order_Payment $payment)
    {
        $comment = $this->_helper()->__('The transaction has been rejected.');

        $payment->setTransactionId($this->_transactionId)
            ->setPreparedMessage($comment)
            ->setIsTransactionApproved(false)
            ->setIsTransactionClosed(true)
            ->deny()
            ->save();

        $payment->addTransaction(Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER)
            ->save();

        $payment->getOrder()
            ->sendOrderUpdateEmail(true, $comment)
            ->save();

        $payment->getOrder()->addStatusHistoryComment($comment)
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
            ->registerCaptureNotification($this->getOrder()->getTotalDue())
            ->save();

        $this->getOrder()
            ->save();

        // notify customer
        if ($invoice = $payment->getCreatedInvoice()) {
            $comment = $this->_helper()->__('Notified customer about invoice #%s.', $invoice->getIncrementId());
            if (!$this->getOrder()->getEmailSent()) {
                $this->getOrder()
                    ->queueNewOrderEmail()
                    ->setIsCustomerNotified(true)
                    ->addStatusHistoryComment($comment)
                    ->save();
            } else {
                $this->getOrder()
                    ->sendOrderUpdateEmail(true, $comment)
                    ->addStatusHistoryComment($comment)
                    ->save();
            }
        }
    }
}
