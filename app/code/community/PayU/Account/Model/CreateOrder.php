<?php

class PayU_Account_Model_CreateOrder
{
    /** @var PayU_Account_Model_Config */
    private $payuConfig;

    /**
     * PayU_Account_Model_CreateOrder constructor.
     * @param string $method
     */
    public function __construct($method)
    {
        $this->payuConfig = Mage::getSingleton('payu/config', array('method' => $method));
    }

    /**
     * @param array $orderData
     * @return object
     * @throws Mage_Core_Exception
     */
    public function execute($orderData)
    {
        $this->payuConfig->initializeOpenPayUConfiguration();

        $orderData['merchantPosId'] = $this->payuConfig->getMerchantPosId();

        try {
            /** @var \OpenPayU_Result $result */
            $result = \OpenPayU_Order::create($orderData);

            if ($result->getStatus() == \OpenPayU_Order::SUCCESS) {
                return $result->getResponse();
            } else {
                Mage::log($result);

                Mage::throwException(Mage::helper('payu')
                    ->__('There was a problem with the payment initialization, please contact system administrator.'));
            }
        } catch (\OpenPayU_Exception $e) {
            Mage::log($e->getMessage());

            Mage::throwException(Mage::helper('payu')
                ->__('There was a problem with the payment initialization, please contact system administrator.'));

        }
    }
}