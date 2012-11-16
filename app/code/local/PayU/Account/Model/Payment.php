<?php

/**
 *    ver. 0.1.6.5.1
 *    PayU -Standard Payment Model
 *
 * @copyright  Copyright (c) 2011-2012 PayU
 * @license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *    http://www.payu.com
 *    http://www.openpayu.com
 *    http://twitter.com/openpayu
 */

require_once('lib/payu/sdk/openpayu.php');

class PayU_Account_Model_Payment extends Mage_Payment_Model_Method_Abstract

{
    /**
     *
     * Configuration
     * @var PayU_Account_Model_Config
     */
    protected $_config;

    /**
     *
     * The base module url
     * @var string
     */
    protected $_myUrl;

    /**
     *
     * Payment method code
     * @var string
     */
    protected $_code = 'payu_account';

    /**
     *
     * Payment method title
     * @var string
     */
    protected $_title = 'PayU account';

    /**
     *
     * Block type
     * @var string
     */
    protected $_formBlockType = 'payu_account/form';

    /**
     *
     * Is initialization needed
     * @var boolean
     */
    protected $_isInitializeNeeded = true;

    /**
     *
     * Can use internal
     * @var boolean
     */
    protected $_canUseInternal = false;

    /**
     *
     * Enter description here ...
     * @var unknown_type
     */
    protected $_canUseForMultishipping = false;

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

    protected $_tempInfo = "AWAITING_PayU";

    /**
     * PayU payment statuses
     *
     * @var string
     */
    const     PAYMENT_STATUS_NEW = 'PAYMENT_STATUS_NEW';
    const     PAYMENT_STATUS_CANCEL = 'PAYMENT_STATUS_CANCEL';
    const     PAYMENT_STATUS_REJECT = 'PAYMENT_STATUS_REJECT';
    const     PAYMENT_STATUS_INIT = 'PAYMENT_STATUS_INIT';
    const     PAYMENT_STATUS_SENT = 'PAYMENT_STATUS_SENT';
    const     PAYMENT_STATUS_NOAUTH = 'PAYMENT_STATUS_NOAUTH';
    const     PAYMENT_STATUS_REJECT_DONE = 'PAYMENT_STATUS_REJECT_DONE';
    const     PAYMENT_STATUS_END = 'PAYMENT_STATUS_END';
    const     PAYMENT_STATUS_ERROR = 'PAYMENT_STATUS_ERROR';

    const NEW_PAYMENT_URL = "payu_account/payment/new";

    /**
     * PayU order statuses
     *
     * @var string
     */

    const     ORDER_STATUS_PENDING = 'ORDER_STATUS_PENDING';
    const     ORDER_STATUS_SENT = 'ORDER_STATUS_SENT';
    const     ORDER_STATUS_COMPLETE = 'ORDER_STATUS_COMPLETE';
    const     ORDER_STATUS_CANCEL = 'ORDER_STATUS_CANCEL';
    const     ORDER_STATUS_REJECT = 'ORDER_STATUS_REJECT';

    /**
     * Constructor
     */
    public function __construct()
    {
        parent::__construct();

        // initialize the PayU config
        $this->initializeOpenPayUConfiguration();
    }

    /**
     * Initializes the payment ...
     *
     * @param Mage_Sales_Model_Order
     * @param Mage_Shipping_Model_Shipping
     * @return array
     */
    public function orderCreateRequest(Mage_Sales_Model_Order $order, $allShippingRates)
    {

        $this->_order = $order;

        /**
         * Setting session identifier
         *
         * @var string
         */
        $sessionid = md5(rand() . rand() . rand() . rand()) . "-" . $this->_order->getRealOrderId() . "-" . $this->_order->getId();


        // store session identifier in session info
        Mage::getSingleton('core/session')->setPayUSessionId($sessionid);

        // assign current transaction id
        $this->_transactionId = $sessionid;

        /** @var string Order Currency Code */
        $orderCurrencyCode = $this->_order->getOrderCurrencyCode();

        /** @var string Country Code */
        $orderCountryCode = $this->_order->getBillingAddress()->getCountry();

        /** @var array assign the shipping info for created order */
        $shippingCostList = array();

        /** @var string Check wether the order is virtual or material */
        $orderType = ($this->_order->getIsVirtual()) ? "VIRTUAL" : "MATERIAL";

        // if the standard paying method has been selected
        if (empty($allShippingRates)) {

            // normal way of paying
            $allShippingRates = Mage::getStoreConfig('carriers', Mage::app()->getStore()->getId());

            $methodArr = explode("_", $this->_order->getShippingMethod());

            foreach ($allShippingRates as $key => $rate) {

                if ($rate['active'] == 1 && $methodArr[0] == $key) {
                    $shippingCostList[] = array(
                        'ShippingCost' => array(
                            'Type' => $rate['title'] . ' - ' . $rate['name'],
                            'CountryCode' => $orderCountryCode,
                            'Price' => array(
                                'Gross' => $this->toAmount($this->_order->getShippingAmount()),
                                'Net' => $this->toAmount($this->_order->getShippingAmount()),
                                'Tax' => $this->toAmount($this->_order->getShippingTaxAmount()),
                                'TaxRate' => $this->toAmount($this->calculateTaxRate()),
                                'CurrencyCode' => $orderCurrencyCode
                            )
                        )
                    );
                }

            }

            $grandTotal = $this->_order->getGrandTotal() - $this->_order->getShippingAmount();

        } else {
            // assigning the shipping costs list
            foreach ($allShippingRates as $rate) {
                $shippingCostList[] = array(
                    'ShippingCost' => array(
                        'Type' => $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle(),
                        'CountryCode' => $orderCountryCode,
                        'Price' => array(
                            'Gross' => $this->toAmount($rate->getPrice()),
                            'Net' => $this->toAmount($rate->getPrice()),
                            'Tax' => $this->toAmount($this->_order->getShippingTaxAmount()),
                            'TaxRate' => $this->toAmount($this->calculateTaxRate()),
                            'CurrencyCode' => $orderCurrencyCode
                        )
                    )
                );
            }

            $grandTotal = $this->_order->getGrandTotal();
        }

        $shippingCost = array(
            'CountryCode' => $orderCountryCode,
            'ShipToOtherCountry' => 'true',
            'ShippingCostList' => $shippingCostList
        );


        /** @var string All items included in the order */
        $orderItems = $this->_order->getAllVisibleItems();

        /** @var array Here is where order items will be processed for PayU purposes */
        $items = array();

        foreach ($orderItems as $key => $item) {
            /** @var array Retrieving item info */
            $itemInfo = $item->getData();

            // Check if the item is countable one
            if ($this->toAmount($itemInfo['price_incl_tax']) > 0) {

                /** Pushing the current item to ShoppingCarItems list */
                $items[]['ShoppingCartItem'] = array(
                    'Quantity' => (int)$itemInfo['qty_ordered'],
                    'Product' => array(
                        'Name' => $itemInfo['name'],
                        'UnitPrice' => array(
                            'Gross' => $this->toAmount($itemInfo['price_incl_tax']),
                            'Net' => $this->toAmount($itemInfo['price']),
                            'Tax' => $this->toAmount($itemInfo['tax_amount']),
                            'TaxRate' => $this->toAmount($itemInfo['tax_percent']),
                            'CurrencyCode' => $orderCurrencyCode
                        )
                    )
                );
            }
        }

        // assigning the shopping cart
        $shoppingCart = array(
            'GrandTotal' => $this->toAmount($grandTotal),
            'CurrencyCode' => $orderCurrencyCode,
            'ShoppingCartItems' => $items
        );

        $orderInfo = array(
            'MerchantPosId' => OpenPayU_Configuration::getMerchantPosId(),
            'SessionId' => $sessionid,
            'OrderUrl' => $this->_myUrl . 'cancelPayment?order=' . $this->_order->getRealOrderId(),
            'OrderCreateDate' => date("c"),
            'OrderDescription' => 'Order no ' . $this->_order->getId(),
            'MerchantAuthorizationKey' => OpenPayU_Configuration::getPosAuthKey(),
            'OrderType' => $orderType,
            'ShoppingCart' => $shoppingCart,
            'ValidityTime' => $this->_config->getOrderValidityTime()
        );


        $OCReq = array(
            'ReqId' => md5(rand()),
            'CustomerIp' => Mage::app()->getFrontController()->getRequest()->getClientIp(),
            'NotifyUrl' => $this->_myUrl . 'orderNotifyRequest',
            'OrderCancelUrl' => $this->_myUrl . 'cancelPayment',
            'OrderCompleteUrl' => $this->_myUrl . 'completePayment',
            'Order' => $orderInfo,
        );

        if (!$this->_order->getIsVirtual()) {
            $OCReq['ShippingCost'] = array(
                'AvailableShippingCost' => $shippingCost,
                'ShippingCostsUpdateUrl' => $this->_myUrl . 'shippingCostRetrieve'
            );
        }

        $customer_sheet = array();

        $billingAddressId = $this->_order->getBillingAddressId();

        if (!empty($billingAddressId)) {

            $billingAddress = $this->_order->getBillingAddress();

            $customer_mail = $billingAddress->getEmail();

            if (!empty($customer_mail)) {
                $customer_sheet = array(
                    'Email' => $billingAddress->getEmail(),
                    'Phone' => $billingAddress->getTelephone(),
                    'FirstName' => $billingAddress->getFirstname(),
                    'LastName' => $billingAddress->getLastname()
                );

                $shippingAddressId = $this->_order->getShippingAddressId();

                if (!empty($shippingAddressId))
                    $shippingAddress = $this->_order->getShippingAddress();

                $customer_sheet['Shipping'] = array(
                    'Street' => trim(implode(' ', $shippingAddress->getStreet())),
                    'PostalCode' => $shippingAddress->getPostcode(),
                    'City' => $shippingAddress->getCity(),
                    'CountryCode' => $shippingAddress->getCountry(),
                    'AddressType' => 'SHIPPING',
                    'RecipientName' => trim($shippingAddress->getFirstname() . ' ' . $shippingAddress->getLastname()),
                    'RecipientPhone' => $shippingAddress->getTelephone(),
                    'RecipientEmail' => $shippingAddress->getEmail()
                );

                $OCReq['Customer'] = $customer_sheet;
            }
        }


        // send message OrderCreateRequest, $result->response = OrderCreateResponse message
        $result = OpenPayU_Order::create($OCReq);

        if ($result->getSuccess()) {

            /** @var array Assigning the redirect form data */
            $result = OpenPayU_OAuth::accessTokenByClientCredentials();
            if ($result->getSuccess()) {
                $locale = Mage::getStoreConfig('general/locale/code', Mage::app()->getStore()->getId());
                $lang_code = explode('_', $locale, 2);

                $ret = array(
                    'url' => OpenPayu_Configuration::getSummaryUrl(),
                    'sessionId' => $sessionid,
                    'oauthToken' => $result->getAccessToken(),
                    'lang' => strtolower($lang_code[1])
                );
            } else {
                /** Something has gone wrong with the $result succession */
                Mage::throwException(Mage::helper('payu_account')->__('There was a problem with initializing the payment, please contact the store administrator. ' . $result->getError() . ' ' . $result->getMessage()));
            }

        } else {
            /** Something has gone wrong with the $result succession */
            Mage::throwException(Mage::helper('payu_account')->__('There was a problem with initializing the payment, please contact the store administrator. ' . $result->getError() . ' ' . $result->getMessage()));
        }

        return $ret;

    }

    /**
     * One Step Checkout initialization
     * @return Mage_Sales_Model_Order
     */
    public function newOneStep()
    {
        $checkout = Mage::getSingleton('checkout/type_onepage');

        $customerSession = Mage::getSingleton('customer/session');

        $checkout->initCheckout();

        // if guest payment
        if (!$customerSession->isLoggedIn()) {
            ;

            $billingAddress = array
            (
                'address_id' => 5,
                'firstname' => $this->_tempInfo,
                'lastname' => $this->_tempInfo,
                'company' => "",
                'street' => array
                (
                    0 => $this->_tempInfo,
                    1 => $this->_tempInfo
                ),
                'city' => $this->_tempInfo,
                'postcode' => $this->_tempInfo,
                'country_id' => Mage::helper('core')->getDefaultCountry(),
                'telephone' => "0000000",
                'save_in_address_book' => 0
            );

            $checkout->saveBilling($billingAddress, false);
            $checkout->saveShipping($billingAddress, false);
            $checkout->saveCheckoutMethod('guest');
            $checkout->getQuote()->setCustomerId(null)
                ->setCustomerIsGuest(true)->setCustomerEmail(md5(rand() . rand()) . "_TEMP_PayU@" . Mage::getModel('payu_account/config')->getDomainName())
                ->setCustomerGroupId(Mage_Customer_Model_Group::NOT_LOGGED_IN_ID);

        } else {
            $customer = $customerSession->getCustomer();
            $checkout->saveBilling($customer->getDefaultBilling(), false);
            $checkout->saveShipping($customer->getDefaultShipping(), false);
            $checkout->saveCheckoutMethod('register');

            $checkout->getQuote()->setCustomerId($customerSession->getCustomerId())
                ->setCustomerIsGuest(false);
        }

        $checkout->getQuote()->getBillingAddress()->setShippingMethod('flatrate_flatrate')->setCollectShippingRates(true);

        // presetting the default shipment method
        $checkout->saveShippingMethod('flatrate_flatrate');
        $checkout->getQuote()->collectTotals()->save();

        // assigning the payment method type
        $checkout->savePayment(array('method' => 'payu_account'));

        $checkout->saveOrder();

        $this->_order = Mage::getModel('sales/order')->load($checkout->getQuote()->getId(), 'quote_id');

        $storeId = Mage::app()->getStore()->getId();
        $paymentHelper = Mage::helper("payment");
        $zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice($storeId);
        if ($paymentHelper->isZeroSubTotal($storeId)
            && $this->_order->getGrandTotal() == 0
            && $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE
            && $paymentHelper->getZeroSubTotalOrderStatus($storeId) == 'pending'
        ) {
            $invoice = $this->_initInvoice();
            $invoice->getOrder()->setIsInProcess(true);
            $transactionSave = Mage::getModel('core/resource_transaction')
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
        }

        return $this->_order;

    }


    /**
     * Processing the BeforeSummary from PayU
     *
     * @return array
     */
    public function beforeSummary()
    {

        // After customer login PayU service redirect back user to merchant with ?code=... paramater.
        // Parameter code is used to retrieve accessToken in OAuth autorization code mode from PayU service.

        $code = Mage::app()->getRequest()->getParam('code');
        $returnUri = $this->_myUrl . "beforeSummary";
        /**
        try {
        $url = OpenPayU_Configuration::$serviceUrl . "user/oauth/authorize";
        OpenPayU::setOpenPayuEndPoint($url);
        $json = OpenPayuOAuth::getAccessTokenByCode($code, OpenPayU_Configuration::$clientId, OpenPayU_Configuration::$clientSecret,$returnUri);
        echo $json->{"payu_user_email"};
        } catch (Exception $ex) {
        echo $ex;
        }*/

        $result = OpenPayU_OAuth::accessTokenByCode($code, $returnUri);

        // checking if result is succeeded
        if ($result->getSuccess()) {

            $sessionId = Mage::getSingleton('core/session')->getPayUSessionId();
            $this->setOrderBySessionId($sessionId);

            $customer = Mage::getModel('customer/customer');

            if ($this->_order->getCustomerIsGuest()) {
                // check if we have the user in the database
                $email = $result->getPayuUserEmail();
                $customer->setWebsiteId(Mage::app()->getWebsite()->getId());
                $customer->loadByEmail($email);

            } else {
                $customer->load($this->_order->getCustomerId());
            }

            // if we are in guest mode
            if (!$customer->getId()) {
                $this->_order->setCustomerEmail($email);
            } else {
                $this->_order->sendNewOrderEmail();
                //$this->_order->setCustomerId($customer->getId());
                //$this->_order->setBillingAddress($customer->getDefaultBilling());
                //$this->_order->setShippingAddress($customer->getDefaultShipping());
            }

            $this->_order->save();


            /** @var array Data needed for redirection to PayU summary page */
            $ret = array(
                'url' => OpenPayu_Configuration::getSummaryUrl(),
                'sessionId' => $sessionId,
                'oauth_token' => $result->getAccessToken(),
                'client_id' => OpenPayU_Configuration::getClientId()
            );

        } else {
            Mage::throwException(Mage::helper('payu_account')->__('There was a problem with the payment initialization, please contact system administrator.') . $result->getError() . ' ' . $result->getMessage());
        }

        return $ret;
    }

    public function completeOrder($order)
    {
        $this->_order = $order;
        $result = $this->orderStatusUpdateRequest(PayU_Account_Model_Payment::ORDER_STATUS_COMPLETE);
        if ($result) {
            $this->updateOrderStatus(PayU_Account_Model_Payment::ORDER_STATUS_COMPLETE);
            return $result;
        }
    }

    public function rejectOrder($order)
    {
        $this->_order = $order;
        $result = $this->orderStatusUpdateRequest(PayU_Account_Model_Payment::ORDER_STATUS_REJECT);
        if ($result) {
            $this->updateOrderStatus(PayU_Account_Model_Payment::ORDER_STATUS_REJECT);
            return $result;
        }
    }

    public function cancelOrder($order)
    {
        $this->_order = $order;
        $result = $this->orderStatusUpdateRequest(PayU_Account_Model_Payment::ORDER_STATUS_CANCEL);
        if ($result) {
            $this->updateOrderStatus(PayU_Account_Model_Payment::ORDER_STATUS_CANCEL);
            return $result;
        }
    }


    /** Complete payment */
    public function completePayment()
    {

        // get some information about the order from PayU
        $sessionId = Mage::getSingleton('core/session')->getPayUSessionId();
        $this->retrieveAndUpdateBySessionId($sessionId);

    }

    /** Cancel payment */
    public function cancelPayment()
    {
        $sessionId = Mage::app()->getRequest()->getParam('sessionId');

        if (!empty($sessionId)) {
            $result = OpenPayU_Order::cancel($sessionId, true);
        }
    }

    /**
     * Retrieve order info from PayU and update billing and shipping info
     * @param string $sessionId
     */
    protected function retrieveAndUpdateBySessionId($sessionId)
    {

        $this->setOrderBySessionId($sessionId);

        $result = OpenPayU_Order::retrieve($sessionId);

        // parse the information
        $response = $result->getResponse();

        $orderRetrieveResponse = $response['OpenPayU']['OrderDomainResponse']['OrderRetrieveResponse'];

        // get Payment status from response
        $payUOrderStatus = $orderRetrieveResponse['OrderStatus'];

        // get Payment status from response
        $payUPaymentStatus = (isset($orderRetrieveResponse['PaymentStatus'])) ? $orderRetrieveResponse['PaymentStatus'] : false;

        // update status of the payment
        if (!empty($payUPaymentStatus))
            $this->updatePaymentStatus($payUPaymentStatus, $payUOrderStatus);

        // update order status if payment status not available
        if (empty($payUPaymentStatus))
            $this->updateOrderStatus($payUOrderStatus);


        if (!empty($orderRetrieveResponse['CustomerRecord'])) {
            // update shipping info of the order
            $this->updateShippingInfo($orderRetrieveResponse);

            // update customer data of the order
            $this->updateCustomerData($orderRetrieveResponse);


        }


    }

    protected function orderStatusUpdateRequest($status)
    {
        $sessionId = $this->_order->getPayment()->getLastTransId();
        $result = OpenPayU_Order::updateStatus($sessionId, $status, true);
        if ($result->getSuccess())
            return $result;
        else
            Mage::log("PayU error while updating status: " . $result->getError());
        return false;
    }

    protected function setOrderBySessionId($sess)
    {

        $orderId = $this->getOrderIdBySessionId($sess);
        $this->_order = Mage::getModel('sales/order')->load($orderId);

    }

    /** @return xml Processing the OrderNotifyRequest from PayU */
    public function orderNotifyRequest()
    {

        /** @var xml get posted document */
        $document = Mage::app()->getRequest()->getPost('DOCUMENT');

        /** if the document is empty return */
        if (empty($document))
            return "error";

        try {
            // Processing notification received from PayU service.
            // Variable $notification contains array with OrderNotificationRequest message.
            $result = OpenPayU_Order::consumeMessage($document);

            if ($result->getMessage() == 'OrderNotifyRequest') {

                /** @var string identify current session Id from document */
                $sessionId = $result->getSessionId();

                $this->_transactionId = $sessionId;

                /** @var array get information about order */
                $orderId = $this->getOrderIdBySessionId($sessionId);

                if ($orderId > 0) {


                    $this->setOrderBySessionId($sessionId);

                    $this->retrieveAndUpdateBySessionId($sessionId);

                }


            } else {
                Mage::log('PayU: There was a problem with PayU orderNotifyRequest, result data:' . serialize($result));
                $ret = "error";
            }

        } catch (Exception $e) {
            Mage::log('PayU: There was a problem with PayU orderNotifyRequest, errorMessage:' . $e->getMessage());
            Mage::log('PayU: OpenPayU_Order::printOutputConsole:' . OpenPayU_Order::printOutputConsole());
            $ret = "error";
        }

    }

    /**
     * Update order's customer information based on PayU information
     * @var array result data from payu with billing and shipping info
     */
    protected function updateCustomerData($data)
    {

        try {

            $customerRecord = $data['CustomerRecord'];

            $this->_order->setCustomerFirstname($customerRecord['FirstName']);
            $this->_order->setCustomerLastname($customerRecord['LastName']);
            $this->_order->setCustomerEmail($customerRecord['Email']);

            $billing = $this->_order->getBillingAddress();

            $billing->setFirstname($customerRecord['FirstName']);
            $billing->setLastname($customerRecord['LastName']);
            $billing->setTelephone($customerRecord['Phone']);

            $this->_order->setBillingAddress($billing)->save();

            if (isset($data['Shipping'])) {

                $shippingAddress = $data['Shipping']['Address'];

                $billing = $this->_order->getBillingAddress();

                $billing->setCity($shippingAddress['City']);
                $billing->setStreet($shippingAddress['Street'] . " " . $shippingAddress['HouseNumber'] . (isset($shippingAddress['ApartmentNumber']) ? " / " . $shippingAddress['ApartmentNumber'] : ''));
                $billing->setPostcode($shippingAddress['PostalCode']);
                $billing->setCountryId($shippingAddress['CountryCode']);

                $this->_order->setBillingAddress($billing)->save();


                $recipient = explode(" ", $shippingAddress['RecipientName']);

                $shipping = $this->_order->getShippingAddress();

                $shipping->setFirstname($recipient[0]);
                $shipping->setLastname($recipient[1]);
                $shipping->setTelephone($customerRecord['Phone']);
                $shipping->setCity($shippingAddress['City']);
                $shipping->setStreet($shippingAddress['Street'] . " " . $shippingAddress['HouseNumber'] . (isset($shippingAddress['ApartmentNumber']) ? " / " . $shippingAddress['ApartmentNumber'] : ''));
                $shipping->setPostcode($shippingAddress['PostalCode']);
                $shipping->setCountryId($shippingAddress['CountryCode']);

                $this->_order->setShippingAddress($shipping)->save();

            } elseif (isset($data['Invoice']['Billing'])) {
                $billingAddress = $data['Invoice']['Billing'];

                $billing = $this->_order->getBillingAddress();

                $billing->setFirstname('');
                $billing->setLastname('');
                $billing->setCompany($billingAddress['RecipientName']);
                $billing->setCity($billingAddress['City']);
                $billing->setStreet($billingAddress['Street'] . " " . $billingAddress['HouseNumber'] . (isset($billingAddress['ApartmentNumber']) ? " / " . $billingAddress['ApartmentNumber'] : ''));
                $billing->setPostcode($billingAddress['PostalCode']);
                $billing->setCountryId($billingAddress['CountryCode']);

                $this->_order->setBillingAddress($billing)->save();
            }

            if (!$this->_order->getEmailSent()) {
                $this->_order->sendNewOrderEmail();
                $this->_order->setEmailSent(1);
            }

            $this->_order->save();

        } catch (Error $e) {
            Mage::logException("Can not update order data: " . $e);
        }

    }

    /**
     *
     * Update shipping info after notifyRequest
     * @param array $data
     */
    protected function updateShippingInfo($data)
    {

        try {

            $typeChosen = $data['Shipping']['ShippingType'];
            $cost = $data['Shipping']['ShippingCost']['Gross'];

            if (!empty($typeChosen)) {

                $quote = Mage::getModel('sales/quote')->load($this->_order->getQuoteId());
                $address = $quote->getShippingAddress();

                $shipping = Mage::getModel('shipping/shipping');
                $shippingRates = $shipping->collectRatesByAddress($address)->getResult();

                #file_put_contents("payu.log", "shipping: " . date("H:i:s") . " " . print_r($shippingRates, 1) . "\n", FILE_APPEND);

                $shippingCostList = array();


                foreach ($shippingRates->getAllRates() as $rate) {
                    $type = $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle();

                    if ($type == $typeChosen) {
                        $this->_order->setShippingDescription($typeChosen);
                        $this->_order->setShippingMethod($rate->getCarrier() . "_" . $rate->getMethod());
                        $current = $this->_order->getShippingAmount();
                        $this->_order->setShippingAmount($cost / 100);
                        $this->_order->setGrandTotal($this->_order->getGrandTotal() + $this->_order->getShippingAmount() - $current);
                        $this->_order->save();
                    }
                }

            }

        } catch (Exception $e) {
            Mage::logException("shipping info error: " . $e);
        }

    }

    /** @return int return orderId by sessionId */
    protected function getOrderIdBySessionId($sess)
    {
        $sessArr = explode("-", $sess);
        if (count($sessArr) < 3)
            return null;
        return $sessArr[2];
    }

    /**
     * Update order status
     * @param string new order status
     */
    protected function updateOrderStatus($orderStatus)
    {

        $payment = $this->_order->getPayment();
        $currentState = $payment->getAdditionalInformation('payu_order_status');

        // change the order status if needed
        if ($currentState != $orderStatus) {
            try {
                switch ($orderStatus) {

                    case PayU_Account_Model_Payment::ORDER_STATUS_CANCEL :
                        $this->updatePaymentStatusCancelled($payment);
                        break;

                    case PayU_Account_Model_Payment::ORDER_STATUS_REJECT :
                        $this->updatePaymentStatusDenied($payment);
                        break;

                    case PayU_Account_Model_Payment::ORDER_STATUS_SENT :
                        $this->updatePaymentStatusInProgress($payment);
                        break;

                    case PayU_Account_Model_Payment::ORDER_STATUS_PENDING :
                        $this->updatePaymentStatusPending($payment);
                        break;

                    default:
                        break;


                }

                $payment->setAdditionalInformation('payu_order_status', $orderStatus)->save();

            } catch (Exception $e) {
                Mage::logException($e);
            }

        }

    }

    /**
     * Update payment status
     * @param $paymentStatus
     * @param $payUOrderStatus
     */
    protected function updatePaymentStatus($paymentStatus, $payUOrderStatus)
    {

        $payment = $this->_order->getPayment();
        $currentState = $payment->getAdditionalInformation('payu_payment_status');


        // change the payment status if needed
        if ($currentState != $paymentStatus) {
            try {
                switch ($paymentStatus) {

                    case PayU_Account_Model_Payment::PAYMENT_STATUS_NEW :
                        $this->updatePaymentStatusNew($payment);
                        break;

                    case PayU_Account_Model_Payment::PAYMENT_STATUS_CANCEL :
                        $this->updatePaymentStatusCancelled($payment);
                        break;

                    case PayU_Account_Model_Payment::PAYMENT_STATUS_REJECT :
                        $this->updatePaymentStatusDenied($payment);
                        break;

                    case PayU_Account_Model_Payment::PAYMENT_STATUS_REJECT_DONE :
                        $this->updatePaymentStatusReturned($payment);
                        break;

                    case PayU_Account_Model_Payment::PAYMENT_STATUS_END :
                        $this->updatePaymentStatusCompleted($payment);
                        break;

                    case PayU_Account_Model_Payment::PAYMENT_STATUS_ERROR :
                        $this->updatePaymentStatusError($payment);
                        break;

                    default:
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
     * Get PayU session namespace
     *
     * @return PayU_Account_Model_Session
     */
    public function getSession()
    {
        return Mage::getSingleton('payu_account/session');
    }

    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout()
    {
        return Mage::getSingleton('checkout/session');
    }

    /**
     * Returns amount in PayU acceptable format
     *
     * @param $val
     */
    protected function toAmount($val)
    {
        return Mage::helper('payu_account')->toAmount($val);
    }

    /**
     * Redirection url
     *
     * @return
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
        return $this->getCheckout()->getQuote();
    }

    /**
     * Update payment status to new
     * @param $payment
     */
    public function updatePaymentStatusNew($payment)
    {
        $transaction = $payment->setTransactionId($this->_transactionId);
        $transaction->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('New transaction started.'));
        $transaction->save();
    }

    /**
     * Change the status to cancelled
     * @param unknown_type $payment
     */
    public function updatePaymentStatusCancelled($payment)
    {
        $transaction = $payment->setTransactionId($this->_transactionId);

        $payment->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('Transaction cancelled.'))
            ->registerVoidNotification();

        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, "PayU - " . Mage::helper('payu_account')->__('The transaction has been cancelled.'), true)
            ->sendOrderUpdateEmail()
            ->save();

        $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
        $transaction->save();
    }

    /**
     * Change the status to rejected
     * @param $payment
     */
    public function updatePaymentStatusDenied($payment)
    {
        $transaction = $payment->setTransactionId($this->_transactionId);

        $payment->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('Transaction rejected.'))
            ->setParentTransactionId($this->_transactionId)
            ->registerVoidNotification();

        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, "PayU - " . Mage::helper('payu_account')->__('The transaction has been rejected.'), true)
            ->sendOrderUpdateEmail()
            ->save();

        $transaction->setTxnType(Mage_Sales_Model_Order_Payment_Transaction::TYPE_VOID);
        $transaction->save();
    }

    /**
     * Change the status to in progress
     * @param $payment
     */
    public function updatePaymentStatusInProgress($payment)
    {
        $transaction = $payment->setTransactionId($this->_transactionId);

        $payment->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('The transaction is in progress.'))
            ->setTransactionId($this->_transactionId) // this is the authorization transaction ID
            ->registerVoidNotification();
    }

    /**
     * Update payment status to pending
     * @param $payment
     */
    public function updatePaymentStatusPending($payment)
    {
        $transaction = $payment->setTransactionId($this->_transactionId);


        $payment->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('Transaction awaits approval.'))
            ->setParentTransactionId($this->_transactionId) // this is the authorization transaction ID
            ->registerVoidNotification();

        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, "PayU - " . Mage::helper('payu_account')->__('The transaction is pending.'), false)
            ->save();

    }


    /**
     * Update payment status to returned and holded
     * @param $payment
     */
    public function updatePaymentStatusReturned($payment)
    {
        $transaction = $payment->setTransactionId($this->_transactionId);

        $payment->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('Transaction returned.'))
            ->registerVoidNotification();

        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, "PayU - " . Mage::helper('payu_account')->__('The transaction has been returned.'), true)
            ->sendOrderUpdateEmail()
            ->save();
    }


    /**
     * Update payment status to complete
     * @param $payment
     */
    public function updatePaymentStatusCompleted($payment)
    {

        $transaction = $payment->setTransactionId($this->_transactionId);
        $transaction->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('The transaction completed successfully.'));

        $payment->setIsTransactionApproved(true);
        $payment->setIsTransactionClosed(true);

        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_COMPLETE, true, "PayU - " . Mage::helper('payu_account')->__('The transaction completed successfully.'), true)
            ->sendOrderUpdateEmail(true, "PayU - " . Mage::helper('payu_account')->__('Thank you.') . " " . Mage::helper('payu_account')->__('The transaction completed successfully.'))
            ->save();
        $transaction->save();
    }

    /**
     * Update payment status to error
     * @param $payment
     */
    public function updatePaymentStatusError($payment)
    {
        $transaction = $payment->setTransactionId($this->_transactionId);

        $payment->setPreparedMessage("PayU - " . Mage::helper('payu_account')->__('The transaction is incorrect.'))
            ->setParentTransactionId($this->_transactionId)
            ->registerVoidNotification();
        $comment = $this->_order->setState(Mage_Sales_Model_Order::STATE_HOLDED, true, "PayU - " . Mage::helper('payu_account')->__('The transaction is incorrect.'))
            ->sendOrderUpdateEmail(true, "PayU - " . Mage::helper('payu_account')->__('The transaction is incorrect.'))
            ->save();
    }

    /** calculating the tax rate */
    protected function calculateTaxRate()
    {
        return ($this->_order->getShippingAmount() > 0) ? $this->_order->getShippingTaxAmount() / $this->_order->getShippingAmount() : 0.0;
    }

    /** Recalculating the costs of shipping */
    public function shippingCostRetrieve()
    {

        $document = Mage::app()->getRequest()->getPost('DOCUMENT');

        $result = OpenPayU_Order::consumeMessage($document);

        $cc = $result->getCountryCode();
        $rspId = $result->getReqId();

        if ($result->getMessage() == 'ShippingCostRetrieveRequest') {

            /** assigning the order */
            $this->setOrderBySessionId($result->getSessionId());

            /** assigning a new country */
            $this->_order->getShippingAddress()->setCountryId($cc);


            $quote = Mage::getModel('sales/quote')->load($this->_order->getQuoteId());
            $address = $quote->getShippingAddress();
            $address->setCountryId($cc);

            $shipping = Mage::getModel('shipping/shipping');
            $shippingRates = $shipping->collectRatesByAddress($address)->getResult();

            $shippingCostList = array();


            //check if visible items equal allitems
            $factor = count($this->_order->getAllItems()) / count($this->_order->getAllVisibleItems());

            foreach ($shippingRates->getAllRates() as $rate) {


                $shippingCostList[] = array(
                    'ShippingCost' => array(
                        'Type' => $rate->getCarrierTitle() . ' - ' . $rate->getMethodTitle(),
                        'CountryCode' => $this->_order->getShippingAddress()->getCountry(),
                        'Price' => array(
                            'Gross' => $this->toAmount($rate->getPrice() / $factor),
                            'Net' => $this->toAmount($rate->getPrice() / $factor),
                            'Tax' => $this->toAmount($this->_order->getShippingTaxAmount()),
                            'TaxRate' => $this->toAmount($this->calculateTaxRate()),
                            'CurrencyCode' => $this->_order->getOrderCurrencyCode()
                        )
                    )
                );

            }

            $arr = array(
                'CountryCode' => $cc,
                'ShipToOtherCountry' => 'true',
                'ShippingCostList' => $shippingCostList
            );

            $this->_order->save();

            $xml = OpenPayU::buildShippingCostRetrieveResponse($arr, $rspId, $cc);

            header("Content-type: text/xml");
            echo $xml;
        }

    }


    /**
     * Get PayU -configuration
     *
     * @return PayU_Account_Model_Config
     */
    protected function getConfig()
    {
        return Mage::getModel('payu_account/config');
    }


    /** Initialize PayU configuration */
    protected function initializeOpenPayUConfiguration()
    {

        $this->_config = $this->getConfig();

        $this->_myUrl = $this->_config->getBaseUrl();

        OpenPayU_Configuration::setEnvironment($this->_config->getEnvironment());
        OpenPayU_Configuration::setMerchantPosId($this->_config->getMerchantPosId());
        OpenPayU_Configuration::setPosAuthKey($this->_config->getPosAuthKey());
        OpenPayU_Configuration::setClientId($this->_config->getClientId());
        OpenPayU_Configuration::setClientSecret($this->_config->getClientSecret());
        OpenPayU_Configuration::setSignatureKey($this->_config->getSignatureKey());


    }

}
