<?php

class PayU_Account_Model_GetPayMethods
{
    /**
     * Credit card code
     */
    const CREDIT_CARD_CODE = 'c';

    /**
     * Test Payment code
     */
    const TEST_PAYMENT_CODE = 't';

    /**
     * Paymethod status ENABLED
     */
    const PAYMETHOD_STATUS_ENABLED = 'ENABLED';

    /**
     * @var array
     */
    private $result = [];

    /**
     * @var PayU_Account_Model_Config
     */
    private $payuConfig;

    /**
     * PayU_Account_Model_GetPayMethods constructor.
     * @param string $method
     */
    public function __construct($method) {
        $this->payuConfig = Mage::getSingleton('payu/config', array('method' => $method));
    }

    public function execute()
    {
        $this->payuConfig->initializeOpenPayUConfiguration();

        try {
            $response = \OpenPayU_Retrieve::payMethods()->getResponse();
            if (isset($response->payByLinks)) {
                $this->result = $response->payByLinks;
                $this->removeTestPayment();
                $this->removeCreditCard();
                $this->sortPaymentMethods($this->payuConfig->getPaytypesOrder());

            }
        } catch (\OpenPayU_Exception $exception) {
            Mage::log(__METHOD__ . ': ' . $exception->getMessage());
        }

        return $this->result;
    }

    /**
     * Remove test payment when disabled
     *
     * @return void
     */
    private function removeTestPayment()
    {
        $this->result = array_filter(
            $this->result,
            function ($payByLink) {
                return !($payByLink->value === self::TEST_PAYMENT_CODE && $payByLink->status !== self::PAYMETHOD_STATUS_ENABLED);
            }
        );
    }

    /**
     * Remove test payment when disabled
     *
     * @return void
     */
    private function removeCreditCard()
    {
        /**
         * @var $payuCardConfig PayU_Account_Model_Config
         */
        $payuCardConfig = Mage::getModel('payu/config', array('method' => PayU_Account_Model_Method_PayuCard::CODE));
        if ($payuCardConfig->isActive()) {
            $this->result = array_filter(
                $this->result,
                function ($payByLink) {
                    return $payByLink->value !== self::CREDIT_CARD_CODE;
                }
            );
        }
    }


    /**
     * Card first, sort by admin, disabled last
     *
     * @param array $paymentMethodsOrder
     * @return void
     */
    private function sortPaymentMethods($paymentMethodsOrder)
    {
        if (count($this->result) < 1) {
            return;
        }

        array_walk(
            $this->result,
            function ($item, $key, $paymentMethodsOrder) {
                if ($item->value == self::CREDIT_CARD_CODE) {
                    $item->sort = 0;
                } else if ($item->status !== self::PAYMETHOD_STATUS_ENABLED) {
                    $item->sort = $key + 200;
                } else if (array_key_exists($item->value, $paymentMethodsOrder)) {
                    $item->sort = $paymentMethodsOrder[$item->value] - 100;
                } else {
                    $item->sort = $key + 100;
                }
            },
            array_flip($paymentMethodsOrder)
        );

        usort(
            $this->result,
            function ($a, $b) {
                return $a->sort - $b->sort;
            }
        );
    }

}
