<?php

class PayU_Account_Block_Form_PayuCard extends PayU_Account_Block_Form_Abstract
{

    protected function _construct()
    {
        parent::_construct();

        $this->setMethodTitle($this->__('Pay by card'));
        $this->setMethodLabelAfterHtml('<img src="' . $this->getCardLogos() . '" alt="PayU" class="formPayuLogo" />');
        $this->setTemplate('payu_account/card_form.phtml');
    }

}
