<?php


class PayU_Account_Model_Method_PayuAccount extends PayU_Account_Model_Method_Abstract
{
    const CODE = 'payu_account';

    const PAY_TYPE = 'pay_type';
    const PAYU_CONDITION = 'payu_condition';

    /**
     * Payment method code
     *
     * @var string
     */
    protected $_code = self::CODE;

    /**
     * Path for payment form block
     *
     * @var string
     */
    protected $_formBlockType = 'payu/form_payuAccount';

    public function assignData($data)
    {
        $result = parent::assignData($data);

        if ($this->_payuConfig->isShowPaytypes()) {

            if (!($data instanceof Varien_Object)) {
                $data = new Varien_Object($data);
            }
            $info = $this->getInfoInstance();

            $info
                ->setAdditionalInformation(self::PAY_TYPE, $data->getData(self::PAY_TYPE))
                ->setAdditionalInformation(self::PAYU_CONDITION, $data->getData(self::PAYU_CONDITION));
        }

        return $result;
    }

    public function validate()
    {
        parent::validate();

        if ($this->_payuConfig->isShowPaytypes()) {
            $info = $this->getInfoInstance();
            $errorMsg = false;

            $paytype = $info->getAdditionalInformation(self::PAY_TYPE);
            $payuCondition = $info->getAdditionalInformation(self::PAYU_CONDITION);

            if (!$paytype) {
                $errorMsg = Mage::helper('payu')->__('Please choose a payment method');
            } else if ($this->_getLanguageCode() === 'pl' && !$payuCondition) {
                $errorMsg = Mage::helper('payu')->__('You must accept the "Terms and Conditions of the single transaction in of PayU"');
            }

            if ($errorMsg) {
                Mage::throwException($errorMsg);
            }
        }

        return $this;
    }

}
