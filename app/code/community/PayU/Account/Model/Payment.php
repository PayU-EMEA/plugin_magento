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

require_once (Mage::getBaseDir('lib').'/payu/sdk_v21/openpayu.php');

class PayU_Account_Model_Payment extends Mage_Payment_Model_Method_Abstract 

{
    /**
     * Configuration
     *
     * @var PayU_Account_Model_Config
     */
    protected $_config;
    
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
     * Payment method title
     *
     * @var string
     */
    protected $_title = 'PayU';
    
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
    protected $_tempInfo = "AWAITING_PayU";
    protected $_isGateway = true;
    protected $_canOrder = true;
    protected $_canAuthorize = true;
    protected $_canCapture = true;
    protected $_canCapturePartial = true;
    protected $_canRefund = true;
    protected $_canRefundInvoicePartial = true;
    protected $_canVoid = false;
    protected $_canUseInternal = false;
    protected $_canUseCheckout = true;
    protected $_canUseForMultishipping = false;
    protected $_canSaveCc = false;
    protected $_canReviewPayment = true;
    protected $_payUOrderResult = null;
    
    const PAYMENT_STATUS_NEW = 'PAYMENT_STATUS_NEW';
    const PAYMENT_STATUS_CANCEL = 'PAYMENT_STATUS_CANCEL';
    const PAYMENT_STATUS_REJECT = 'PAYMENT_STATUS_REJECT';
    const PAYMENT_STATUS_INIT = 'PAYMENT_STATUS_INIT';
    const PAYMENT_STATUS_SENT = 'PAYMENT_STATUS_SENT';
    const PAYMENT_STATUS_NOAUTH = 'PAYMENT_STATUS_NOAUTH';
    const PAYMENT_STATUS_REJECT_DONE = 'PAYMENT_STATUS_REJECT_DONE';
    const PAYMENT_STATUS_END = 'PAYMENT_STATUS_END';
    const PAYMENT_STATUS_ERROR = 'PAYMENT_STATUS_ERROR';
    
    const NEW_PAYMENT_URL = "payu_account/payment/new";
    
    const ORDER_STATUS_COMPLETE = 'ORDER_STATUS_COMPLETE';
    const ORDER_STATUS_CANCEL = 'ORDER_STATUS_CANCEL';
    const ORDER_STATUS_REJECT = 'ORDER_STATUS_REJECT';
    
    const ORDER_V2_NEW = 'NEW';
    const ORDER_V2_PENDING =  'PENDING';
    const ORDER_V2_CANCELED = 'CANCELED';
    const ORDER_V2_REJECTED = 'REJECTED';
    const ORDER_V2_COMPLETED = 'COMPLETED';
    const ORDER_V2_WAITING_FOR_CONFIRMATION = 'WAITING_FOR_CONFIRMATION';

    public function __construct() {
        parent::__construct ();
        $this->initializeOpenPayUConfiguration ();
    }

    public function getTitle() {
        return $this->_title;
    }
    
    /**
     * Initializes the payment.
     * @param Mage_Sales_Model_Order
     * @param Mage_Shipping_Model_Shipping
     * @return array
     */
    public function orderCreateRequest(Mage_Sales_Model_Order $order, $allShippingRates) {
        $this->_order = $order;

        $orderCurrencyCode = $this->_order->getOrderCurrencyCode ();
        $orderCountryCode = $this->_order->getBillingAddress ()->getCountry ();
        $shippingCostList = array ();
        
        if (empty ( $allShippingRates ) || Mage::getSingleton ( 'customer/session' )->isLoggedIn()) {
        	
        	if($order->getShippingInclTax() > 0) {
        		$shippingCostList ['shippingMethods'] [] = array(
        		 'name' => $order->getShippingDescription(),
        		 'country' => $orderCountryCode,
        		 'price' => $this->toAmount($order->getShippingInclTax()),
        		);
        	}
        	
        	$grandTotal = $this->_order->getGrandTotal () - $order->getShippingInclTax();
            
        
        } else {
            
        	$firstPrice = 0;
        	
        	foreach ( $allShippingRates as $key => $rate ) {
                
        		$gross = $this->toAmount ( $rate->getPrice () );
        		
        		if($key == 0)
        			$firstPrice = $rate->getPrice ();
                
                $shippingCostList ['shippingMethods'] [] = array (
                        'name' => $rate->getMethodTitle (),
                		'country' => $orderCountryCode,
                		'price' => $gross 
                );

            }

            $grandTotal = $this->_order->getGrandTotal () - $firstPrice;

        }
        
        $shippingCost = array (
                'countryCode' => $orderCountryCode,'shipToOtherCountry' => 'true','shippingCostList' => $shippingCostList 
        );
        
        $orderItems = $this->_order->getAllVisibleItems ();
        
        $items = array ();
        $productsTotal = 0;
        
        $is_discount = false;
        
        foreach ( $orderItems as $key => $item ) {

            $itemInfo = $item->getData ();
            
            if($itemInfo ['discount_amount'] > 0){
                $itemInfo ['price_incl_tax'] = $itemInfo ['price_incl_tax'] - $itemInfo ['discount_amount'];
                $is_discount = true;
            }
            
            else if($itemInfo ['discount_percent'] > 0)
                $itemInfo ['price_incl_tax'] = $itemInfo ['price_incl_tax'] * (100 - $itemInfo ['discount_percent']) / 100;
            
            // Check if the item is countable one
            if ($this->toAmount ( $itemInfo ['price_incl_tax'] ) > 0) {
                $items ['products'] [] = array (
                        'quantity' => ( int ) $itemInfo ['qty_ordered'],'name' => $itemInfo ['name'],'unitPrice' => $this->toAmount ( $itemInfo ['price_incl_tax'] ) 
                                );
                $productsTotal += $itemInfo ['price_incl_tax'] * $itemInfo ['qty_ordered'];
            }
        }
        
        //if($this->_order->getShippingAmount () > 0 && !empty ( $shippingCostList['shippingMethods'][0] ) ){
        //        $items ['products'] ['products'] [] = array (
        //            'quantity' => 1 ,'name' => Mage::helper ( 'payu_account' )->__('Shipping costs') . " - " . $shippingCostList['shippingMethods'][0]['name'] ,'unitPrice' => $this->toAmount ( $this->_order->getShippingAmount () ));
        //}
        
        // assigning the shopping cart
        $shoppingCart = array (
                'grandTotal' => $this->toAmount ( $grandTotal ),'CurrencyCode' => $orderCurrencyCode,'ShoppingCartItems' => $items 
        );
        
        $orderInfo = array (
                'merchantPosId' => OpenPayU_Configuration::getMerchantPosId (),'orderUrl' => Mage::getBaseUrl () . 'sales/order/view/order_id/' . $this->_order->getId () . '/','description' => 'Order no ' . $this->_order->getRealOrderId (),'validityTime' => $this->_config->getOrderValidityTime () 
        );
        
        if($is_discount){
            $items ['products'] = array();
            $items ['products'][] = array (
                    'quantity' => 1,'name' => Mage::helper ( 'payu_account' )->__('Order # ') . $this->_order->getId (),'unitPrice' => $this->toAmount ( $grandTotal )
            );
        }
        
        $OCReq = $orderInfo;
        $OCReq ['products'] = $items ['products'];
        $OCReq ['customerIp'] = Mage::app ()->getFrontController ()->getRequest ()->getClientIp ();
        $OCReq ['notifyUrl'] = $this->_myUrl . 'orderNotifyRequest';
        $OCReq ['cancelUrl'] = $this->_myUrl . 'cancelPayment';
        $OCReq ['continueUrl'] = $this->_myUrl . 'continuePayment';
        $OCReq ['currencyCode'] = $orderCurrencyCode;
        $OCReq ['totalAmount'] = $shoppingCart ['grandTotal'];
        $OCReq ['extOrderId'] = $this->_order->getId ().'-'.microtime();
        if(!empty($shippingCostList))
        	$OCReq ['shippingMethods'] = $shippingCostList['shippingMethods'];
        unset ( $OCReq ['shoppingCart'] );
        $customer_sheet = array ();
        
        $billingAddressId = $this->_order->getBillingAddressId ();
        
        if (! empty ( $billingAddressId )) {
            
            $billingAddress = $this->_order->getBillingAddress ();
            
            $customer_mail = $billingAddress->getEmail ();
            
            if (! empty ( $customer_mail )) {
                
                $customer_sheet = array (
                        'email' => $billingAddress->getEmail (),'phone' => $billingAddress->getTelephone (),'firstName' => $billingAddress->getFirstname (),'lastName' => $billingAddress->getLastname () 
                );
                
                $shippingAddressId = $this->_order->getShippingAddressId ();
                
                if (! empty ( $shippingAddressId )) {
                    $shippingAddress = $this->_order->getShippingAddress ();
                }
                
                if (! $this->_order->getIsVirtual ()) {
                    $customer_sheet ['delivery'] = array (
                            'street' => trim ( implode ( ' ', $shippingAddress->getStreet () ) ),'postalCode' => $shippingAddress->getPostcode (),'city' => $shippingAddress->getCity (),'countryCode' => $shippingAddress->getCountry (),
                            'recipientName' => trim ( $shippingAddress->getFirstname () . ' ' . $shippingAddress->getLastname () ),'recipientPhone' => $shippingAddress->getTelephone (),'recipientEmail' => $shippingAddress->getEmail () 
                    );
                }
                
                $OCReq ['buyer'] = $customer_sheet;
            }
        }

        $result = OpenPayU_Order::create($OCReq);

        if ($result->getStatus () == 'SUCCESS') {
            
            // store session identifier in session info
            Mage::getSingleton ( 'core/session' )->setPayUSessionId ( $result->getResponse ()->orderId );
            
            // assign current transaction id
            $this->_transactionId = $result->getResponse ()->orderId;
            $order->getPayment()->setLastTransId($this->_transactionId);
            
            
            $locale = Mage::getStoreConfig ( 'general/locale/code', Mage::app ()->getStore ()->getId () );
            $lang_code = explode ( '_', $locale, 2 );
            
            $ret = array (
                    'redirectUri' => $result->getResponse ()->redirectUri,'url' => OpenPayu_Configuration::getSummaryUrl (),'sessionId' => $result->getResponse ()->orderId,'lang' => strtolower ( $lang_code [1] ) 
            );
            
            $customer = Mage::getModel ( 'customer/customer' );
            
            if ($this->_order->getCustomerIsGuest ()) {
                $email = $billingAddress->getEmail ();
                $customer->setWebsiteId ( Mage::app ()->getWebsite ()->getId () );
                $customer->loadByEmail ( $email );
            
            } else {
                $customer->load ( $this->_order->getCustomerId () );
            }
            
            if (! $customer->getId ()) {
                $this->_order->setCustomerEmail ( $email );
            }
            
            $this->_order->sendNewOrderEmail ();

            $this->_order->save ();
        
        } else {
            Mage::throwException ( Mage::helper ( 'payu_account' )->__ ( 'There was a problem with initializing the payment, please contact the store administrator. ' . $result->getError () ) );
        }
        
        return $ret;
    
    }


    /**
     * Refund payment
     *
     * @param Varien_Object $payment
     * @param float $amount
     * @return $this
     */
    public function refund(Varien_Object $payment, $amount){
        $order = $payment->getOrder();
        $this->_order = $order;
        $amount = $amount*100;
        $result = OpenPayU_Refund::create($this->_order->getPayment ()->getLastTransId(), 'Magento payu refund', $amount);

        Mage::log($result,null, 'orderRefund.log');

        if ($result->getStatus() == 'SUCCESS') {
            return $this;
        }

        $serverMsg = OpenPayU_Util::statusDesc($result->getStatus());
        $errorMsg = $this->_getHelper()->__($serverMsg);
        Mage::throwException($errorMsg);

    }
    
    /**
     * One Step Checkout initialization
     *
     * @return Mage_Sales_Model_Order
     */
    public function newOneStep() {
        
        $checkout = Mage::getSingleton ( 'checkout/type_onepage' );
        $customerSession = Mage::getSingleton ( 'customer/session' );
        $checkout->initCheckout ();
        
        // if guest payment
        if (! $customerSession->isLoggedIn ()) {
            
            $billingAddress = array (
                    'address_id' => 5,'firstname' => $this->_tempInfo,'lastname' => $this->_tempInfo,'company' => "",'street' => array (
                            0 => $this->_tempInfo,1 => $this->_tempInfo 
                    ) ,'region_id' => '',
    'region' => '','city' => $this->_tempInfo,'postcode' => $this->_tempInfo,'country_id' => Mage::helper ( 'core' )->getDefaultCountry (),'telephone' => "0000000",'save_in_address_book' => 0 
            );
            
            $checkout->saveBilling ( $billingAddress, false );
            $checkout->saveShipping ( $billingAddress, false );
            $checkout->saveCheckoutMethod ( 'guest' );
            $checkout->getQuote ()->setCustomerId ( null )->setCustomerIsGuest ( true )->setCustomerEmail ( md5 ( rand () . rand () ) . "_TEMP_PayU@" . Mage::getModel ( 'payu_account/config' )->getDomainName () )->setCustomerGroupId ( Mage_Customer_Model_Group::NOT_LOGGED_IN_ID );
        
        } else {
            $customer = $customerSession->getCustomer ();
            
            $billing = $checkout->getQuote ()->getBillingAddress ();
            $shipping = $checkout->getQuote ()->isVirtual () ? null : $checkout->getQuote ()->getShippingAddress ();
            
            $customerBilling = $billing->exportCustomerAddress ();
            
            $billing->setCustomerAddress ( $customerBilling );
            $customerBilling->setIsDefaultBilling ( true );
            
            if ($shipping && ! $shipping->getSameAsBilling ()) {
                $customerShipping = $shipping->exportCustomerAddress ();
                $shipping->setCustomerAddress ( $customerShipping );
                $customerShipping->setIsDefaultShipping ( true );
            } else {
                $customerBilling->setIsDefaultShipping ( true );
            }
            
            $checkout->saveCheckoutMethod ( 'register' );
            
            $checkout->getQuote ()->setCustomerId ( $customerSession->getCustomerId () )->setCustomerIsGuest ( false );
        }
        
        $checkout->getQuote ()->getBillingAddress ()->setShippingMethod ( 'flatrate_flatrate' )->setCollectShippingRates ( true )->save();
        $checkout->getQuote ()->getShippingAddress()->setShippingMethod ( 'flatrate_flatrate' )->setCollectShippingRates ( true )->save();
        
        // presetting the default shipment method
        $checkout->saveShippingMethod ( 'flatrate_flatrate' );
        $checkout->getQuote ()->collectTotals ()->save ();
        
        // assigning the payment method type
        $checkout->savePayment ( array (
                'method' => $this->_code 
        ) );
        
        $checkout->saveOrder ();
        
        $this->_order = Mage::getModel ( 'sales/order' )->load ( $checkout->getQuote ()->getId (), 'quote_id' );
        
        $storeId = Mage::app ()->getStore ()->getId ();
        $paymentHelper = Mage::helper ( "payment" );
        $zeroSubTotalPaymentAction = $paymentHelper->getZeroSubTotalPaymentAutomaticInvoice ( $storeId );
        if ($paymentHelper->isZeroSubTotal ( $storeId ) && $this->_order->getGrandTotal () == 0 && $zeroSubTotalPaymentAction == Mage_Payment_Model_Method_Abstract::ACTION_AUTHORIZE_CAPTURE && $paymentHelper->getZeroSubTotalOrderStatus ( $storeId ) == 'pending') {
            $invoice = $this->_initInvoice ();
            $invoice->getOrder ()->setIsInProcess ( true );
            $transactionSave = Mage::getModel ( 'core/resource_transaction' )->addObject ( $invoice )->addObject ( $invoice->getOrder () );
            $transactionSave->save ();
        }
        
        return $this->_order;
    
    }
    
    /**
     *
     * @param Mage_Payment_Model_Info $payment            
     * @return bool
     */
    public function acceptPayment(Mage_Payment_Model_Info $payment) {
        parent::acceptPayment ( $payment );
        
        $sessionId = $payment->getLastTransId ();
        
        if (empty ( $sessionId )) {
            return false;
        }
        
        if (! $this->orderStatusUpdateRequest ( self::ORDER_V2_COMPLETED, $sessionId )) {
            return false;
        }
        
        return true;
    }
    
    /**
     *
     * @param Mage_Payment_Model_Info $payment            
     * @return bool
     */
    public function denyPayment(Mage_Payment_Model_Info $payment) {
        parent::denyPayment ( $payment );
        
        $sessionId = $payment->getLastTransId ();
        
        if (empty ( $sessionId )) {
            return false;
        }
        
        if (! $this->orderStatusUpdateRequest ( self::ORDER_V2_REJECTED, $sessionId )) {
            return false;
        }
        
        return true;
    }
    
    /**
     *
     * @param
     *            $order
     * @return bool OpenPayU_Result
     */
    public function completeOrder($order) {
        
        $this->_order = $order;
        $result = $this->orderStatusUpdateRequest ( self::ORDER_V2_COMPLETED, $this->_order->getPayment ()->getLastTransId () );
        return $result;
        
    }
    
    /**
     *
     * @param $order
     * @return bool
     */
    public function rejectOrder($order) {
        $this->_order = $order;
        return $this->cancelOrder($this->_order);
    }
    
    /**
     *
     * @param $order
     * @return bool
     */
    public function cancelOrder($order) {
        
        $this->_order = $order;
        $result = OpenPayU_Order::cancel($this->_order->getPayment ()->getLastTransId ());
        if ($result->getStatus() == 'SUCCESS') {
            //$this->updateOrderStatus ( self::ORDER_V2_CANCELED );
            return true;
        }
        return false;
    }
    
    /**
     * Cancel payment
     */
    public function cancelPayment() {
        
        $sessionId = Mage::app ()->getRequest ()->getParam ( 'sessionId' );
        if (! empty ( $sessionId )) {
            $result = OpenPayU_Order::cancel ( $sessionId );
        }
        
    }
    
    protected function retrieveAndUpdateByOrderRetrieved( $orderRetrieved ) {
    
        $this->setOrderByOrderId ( $orderRetrieved->extOrderId );

        $payUOrderStatus = $orderRetrieved->status;
    
        $payUPaymentStatus = $orderRetrieved->status;
        
        if(isset($orderRetrieved->payMethod->type))
            $this->_payuPayMethod = $orderRetrieved->payMethod->type;
        
        $this->updatePaymentStatus ( $payUPaymentStatus, $payUOrderStatus );
        
        if(!empty($orderRetrieved->buyer)){
            $this->updateCustomerData($orderRetrieved->buyer);
            //$this->updateShippingInfo($response->orders->orders[0]->buyer);
        }
    
    }
    
    /**
     * @param $status
     * @param  $sessionId
     * @return bool OpenPayU_Result
     */
    protected function orderStatusUpdateRequest($status, $sessionId) {
        
        if (empty ( $sessionId )) {
            $sessionId = $this->_order->getPayment ()->getLastTransId ();
        }
        
        if (empty ( $sessionId )) {
            Mage::log ( "PayU sessionId empty: " . $this->getId () );
            return false;
        }
        
        $status_update = array(
                "orderId" => stripslashes($sessionId),
                "orderStatus" => $status
        );
        
        $result = OpenPayU_Order::statusUpdate ( $status_update );
        
        if ($result) {
            return true;
        } else {
            Mage::log ( "PayU error while updating status: " . $result->getError () );
        }
        
        return $result;
    }
    
    protected function setOrderByOrderId( $extOrderId ) {
        $this->_order = Mage::getModel ( 'sales/order' )->load ( $extOrderId );
    }
    
    public function orderNotifyRequest() {
        
        $body = file_get_contents ( 'php://input' );
        $data = trim ( $body );    
        
        $result = OpenPayU_Order::consumeNotification ( $data );
        $response = $result->getResponse();
        if ($response->order->orderId) {
            
            $this->_transactionId = $response->order->orderId;

            $extOrderIdExploded = $pieces = explode("-", $response->order->extOrderId);
            $orderId = $extOrderIdExploded[0];
//            Mage::log(print_r($orderId, true),null, 'notification.log');
            
            $this->setOrderByOrderId ( $orderId );

            if($response->order->status == 'COMPLETED' && $this->_order->getState() == Mage_Sales_Model_Order::STATE_PROCESSING){
                header("HTTP/1.1 200 OK");
                exit;
            }

            $this->retrieveAndUpdateByOrderRetrieved ( $response->order );
            //the response should be status 200
            header("HTTP/1.1 200 OK");
        }
        exit;
    }
    
    /**
     * Update order's customer information based on PayU information
     * @var array result data from payu with billing and shipping info
     */
    protected function updateCustomerData($data)
    {
        
        try {
            
            $customerRecord = $data;
    
            $this->_order->setCustomerFirstname($customerRecord->firstName);
            $this->_order->setCustomerLastname($customerRecord->lastName);
            $this->_order->setCustomerEmail($customerRecord->email);
    
            if (isset($data->delivery) && !empty( $data->delivery )) {
                
                $shippingAddress = $data->delivery;
    
                /* $billing = $this->_order->getBillingAddress();
    
                $billing->setCity($shippingAddress->city);
                $billing->setStreet($shippingAddress->street);
                $billing->setPostcode($shippingAddress->postalCode);
                $billing->setCountryId($shippingAddress->countryCode);
                
                $this->_order->setBillingAddress($billing)->save(); */
    
                $recipient = explode(" ", $shippingAddress->recipientName);
    
                $shipping = $this->_order->getShippingAddress();
    
                $shipping->setFirstname($recipient[0]);
                $shipping->setLastname($recipient[1]);
                //$shipping->setTelephone($customerRecord['phone']);
                $shipping->setCity($shippingAddress->city);
                $shipping->setStreet($shippingAddress->street);
                $shipping->setPostcode($shippingAddress->postalCode);
                $shipping->setCountryId($shippingAddress->countryCode);
    
                $this->_order->setShippingAddress($shipping)->save();
    
            }
    
            /* if (isset($data['billing']) && !empty( $data['billing'] )) {
                $billingAddress = $data['billing'];
    
                $billing = $this->_order->getBillingAddress();
    
                $recipient = explode(" ", $billingAddress['recipientName']);
                
                $billing->setFirstname($recipient[0]);
                $billing->setLastname($recipient[1]);
                $billing->setCompany($billingAddress['recipientName']);
                $billing->setTelephone($customerRecord['phone']);
                $billing->setCity($billingAddress['city']);
                $billing->setStreet($billingAddress['street']);
                $billing->setPostcode($billingAddress['postalCode']);
                $billing->setCountryId($billingAddress['countryCode']);
                //$this->_order->setCustomerTaxvat($billingAddress['TIN']);
    
                $this->_order->setBillingAddress($billing)->save();
            }
            else
            { 
                $billing = $this->_order->getBillingAddress();
    
                $billing->setFirstname($customerRecord['firstName']);
                $billing->setLastname($customerRecord['lastName']);
                $billing->setTelephone($customerRecord['phone']);
    
                $this->_order->setBillingAddress($billing)->save();
            }  */
    
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

    
    /**
     * Update payment status
     *
     * @param $paymentStatus
     * @param $payUOrderStatus
     */
    protected function updatePaymentStatus($paymentStatus, $payUOrderStatus) {
        
        $payment = $this->_order->getPayment ();
        $currentState = $payment->getAdditionalInformation ( 'payu_payment_status' );

        if(($currentState == self::ORDER_V2_COMPLETED && $paymentStatus == self::ORDER_V2_PENDING) || ($currentState == self::ORDER_V2_COMPLETED && $paymentStatus == self::ORDER_V2_COMPLETED))
            return;

        if ($currentState != $paymentStatus) {
            try {
                switch ($paymentStatus) {
                    case self::ORDER_V2_NEW:
                        $this->updatePaymentStatusNew ( $payment );
                        break;
                        
                    case self::ORDER_V2_PENDING:
                        $this->updatePaymentStatusPending ( $payment );
                        break;
                        
                    case self::ORDER_V2_CANCELED:
                        $this->updatePaymentStatusCanceled ( $payment );
                        break;
                        
                    case self::ORDER_V2_REJECTED:
                        $this->updatePaymentStatusDenied ( $payment );
                        break;
                        
                    case self::ORDER_V2_COMPLETED:
                        $this->updatePaymentStatusCompleted ( $payment );
                        break;
                }
                // set current PayU status information and save
                $payment->setAdditionalInformation ( 'payu_payment_status', $paymentStatus )->save ();
            } catch ( Exception $e ) {
                Mage::logException ( $e );
            }
        }
    }
    
    /**
     * Get PayU session namespace
     *
     * @return PayU_Account_Model_Session
     */
    public function getSession() {
        return Mage::getSingleton ( 'payu_account/session' );
    }
    
    /**
     * Get checkout session namespace
     *
     * @return Mage_Checkout_Model_Session
     */
    public function getCheckout() {
        return Mage::getSingleton ( 'checkout/session' );
    }
    
    /**
     * Returns amount in PayU acceptable format
     *
     * @param $val
     */
    protected function toAmount($val) {
        return Mage::helper ( 'payu_account' )->toAmount ( $val );
    }
    
    /**
     * Redirection url
     *
     * @return string
     */
    public function getOrderPlaceRedirectUrl() {
        return Mage::getUrl ( 'payu_account/payment/new', array (
                '_secure' => true 
        ) );
    }
    
    /**
     * Get current quote
     *
     * @return Mage_Sales_Model_Quote
     */
    public function getQuote() {
        return $this->getCheckout ()->getQuote ();
    }
    
    /**
     * Update payment status to new
     *
     * @param $payment
     */
    public function updatePaymentStatusNew($payment) {
        $payment->setTransactionId ( $this->_transactionId );
        $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'New transaction started.' ) );
        $payment->save ();
        $this->_order->setState ( Mage_Sales_Model_Order::STATE_NEW, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'New transaction started.' ), true )->sendOrderUpdateEmail ()->save ();
    }
    
    /**
     * Change the status to canceled
     *
     * @param unknown_type $payment            
     */
    public function updatePaymentStatusCanceled($payment) {
        $payment->setTransactionId ( $this->_transactionId );
        $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'Transaction canceled.' ) );
        $this->_order->setState ( Mage_Sales_Model_Order::STATE_CANCELED, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction has been canceled.' ), true )->sendOrderUpdateEmail ()->save ();
    }
    
    /**
     * Change the status to rejected
     *
     * @param $payment
     */
    public function updatePaymentStatusDenied($payment) {
        $payment->setTransactionId ( $this->_transactionId );
        $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'Transaction rejected.' ) )->setParentTransactionId ( $this->_transactionId );
        $this->_order->setState ( Mage_Sales_Model_Order::STATE_CANCELED, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction has been rejected.' ), true )->sendOrderUpdateEmail ()->save ();
    }
    
    /**
     * Update payment status to sent
     *
     * @param $payment
     */
    public function updatePaymentStatusSent($payment) {
        if ($this->_order->getState () != Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW) {
            $payment->setTransactionId ( $this->_transactionId );
            $payment->setIsTransactionApproved ( false );
            $payment->setIsTransactionClosed ( false );
            $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER );
            $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'Transaction awaits approval.' ) );
            $this->_order->setState ( Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'Transaction awaits approval.' ), false )->save ();
        }
    }
    
    /**
     * Update payment status to returned and holded
     *
     * @param
     *            $payment
     */
    public function updatePaymentStatusReturned($payment) {
        $payment->setTransactionId ( $this->_transactionId );
        $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'Transaction returned.' ) );
        $this->_order->setState ( Mage_Sales_Model_Order::STATE_HOLDED, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction has been returned.' ), true )->sendOrderUpdateEmail ()->save ();
    }
    
    /**
     * Update payment status to pending
     *
     * @param $payment
     */
    public function updatePaymentStatusPending($payment) {
        if ($this->_order->getState () != Mage_Sales_Model_Order::STATE_PENDING_PAYMENT) {
            $payment->setTransactionId ( $this->_transactionId );
            $payment->setIsTransactionApproved ( false );
            $payment->setIsTransactionClosed ( false );
            $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER );
            $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction is pending.' ) );
            $this->_order->setState ( Mage_Sales_Model_Order::STATE_PENDING_PAYMENT, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction is pending.' ), false )->save ();
        }
    }
    
    /**
     * Update payment status to complete
     *
     * @param $payment
     */
    public function updatePaymentStatusCompleted($payment) {
        if ($this->_order->getState () != Mage_Sales_Model_Order::STATE_PROCESSING) {
            $payment->setTransactionId ( $this->_transactionId );
            $payment->setIsTransactionApproved ( true );
            $payment->setIsTransactionClosed ( true );
            $payment->addTransaction ( Mage_Sales_Model_Order_Payment_Transaction::TYPE_ORDER );
            if(isset($this->_payuPayMethod)){
                
                if($this->_payuPayMethod == "PBL")
                    $method = Mage::helper( 'payu_account' )->__ ('Pay by link');
                
                if($this->_payuPayMethod == "CARD_TOKEN")
                    $method = Mage::helper( 'payu_account' )->__ ('Pay with card');
            }
                
            if(isset($method)){
                $payment->setPreparedMessage ( "PayU - " . $method . " " . Mage::helper ( 'payu_account' )->__ ( 'The transaction completed successfully.' ) );
                $this->_order->setState ( Mage_Sales_Model_Order::STATE_PROCESSING, true, "PayU - " . $method . " " . Mage::helper ( 'payu_account' )->__ ( 'The transaction completed successfully.' ), false )->sendOrderUpdateEmail ( true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction completed successfully in PayU.' ) )->save ();                
            }else{
                $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction completed successfully.' ) );
                $this->_order->setState ( Mage_Sales_Model_Order::STATE_PROCESSING, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction completed successfully.' ), false )->sendOrderUpdateEmail ( true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction completed successfully in PayU.' ) )->save ();
            }
        }
    }
    
    /**
     * Update payment status to error
     *
     * @param $payment
     */
    public function updatePaymentStatusError($payment) {
        $payment->setTransactionId ( $this->_transactionId );
        $payment->setPreparedMessage ( "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction is incorrect.' ) )->setParentTransactionId ( $this->_transactionId );
        $this->_order->setState ( Mage_Sales_Model_Order::STATE_PAYMENT_REVIEW, true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction is incorrect.' ) )->sendOrderUpdateEmail ( true, "PayU - " . Mage::helper ( 'payu_account' )->__ ( 'The transaction is incorrect.' ) )->save ();
    }
    
    /**
     * calculating the tax rate
     */
    protected function calculateTaxRate() {
        return ($this->_order->getShippingAmount () > 0) ? $this->_order->getShippingTaxAmount () / $this->_order->getShippingAmount () : 0.0;
    }
    
    /**
     * Get PayU -configuration
     *
     * @return PayU_Account_Model_Config
     */
    protected function getConfig() {
        return Mage::getModel ( 'payu_account/config' );
    }
    
    /**
     * Initialize PayU configuration
     */
    protected function initializeOpenPayUConfiguration() {

        $this->_config = $this->getConfig ();
        $this->_myUrl = $this->_config->getBaseUrl ();
        
        OpenPayU_Configuration::setApiVersion ( 2.1 );
        OpenPayU_Configuration::setEnvironment ( 'secure' );
        OpenPayU_Configuration::setMerchantPosId ( $this->_config->getMerchantPosId () );
        OpenPayU_Configuration::setSignatureKey ( $this->_config->getSignatureKey () );
        OpenPayU_Configuration::setSender("Magento ver " . Mage::getVersion() . "/Plugin ver " . $this->_config->getPluginVersion());
    
    }

}
