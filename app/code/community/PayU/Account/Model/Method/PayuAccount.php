<?php


class PayU_Account_Model_Method_PayuAccount extends PayU_Account_Model_Method_Abstract
{
    const CODE = 'payu_account';

    const PAY_TYPE = 'pay_type';
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
        $val = null;

        if (is_array($data)) {
            $val = isset($data[self::PAY_TYPE]) ? $data[self::PAY_TYPE] : null;
        } elseif ($data instanceof Varien_Object) {
            $val = $data->getData(self::PAY_TYPE);
        }

        $this->getInfoInstance()->setAdditionalInformation(self::PAY_TYPE, $val);

        return $result;
    }


}
