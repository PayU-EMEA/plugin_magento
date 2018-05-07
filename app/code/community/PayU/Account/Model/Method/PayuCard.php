<?php


class PayU_Account_Model_Method_PayuCard extends PayU_Account_Model_Method_Abstract
{
    const CODE = 'payu_card';

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
    protected $_formBlockType = 'payu/form_payuCard';

}
