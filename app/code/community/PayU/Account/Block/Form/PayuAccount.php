<?php

class PayU_Account_Block_Form_PayuAccount extends PayU_Account_Block_Form_Abstract
{

    protected function _construct()
    {
        parent::_construct();

        $this->setMethodTitle($this->__('Pay with PayU'));
        $this->setMethodLabelAfterHtml('<img src="' . $this->getPayuLogo() . '" alt="PayU" class="formPayuLogo" />');
        $this->setTemplate('payu_account/form.phtml');

    }

    public function getPayMethods()
    {
        /** @var PayU_Account_Model_Config $payuConfig */
        $payuConfig = Mage::getSingleton('payu/config', array('method' => PayU_Account_Model_Method_PayuAccount::CODE));

        if (!$payuConfig->isShowPaytypes()) {
            return null;
        }

        /** @var PayU_Account_Model_GetPayMethods $getPayMetods */
        $getPayMetods = Mage::getModel('payu/getPayMethods', array('method' => PayU_Account_Model_Method_PayuCard::CODE));

        return $getPayMetods->execute();
    }
}
