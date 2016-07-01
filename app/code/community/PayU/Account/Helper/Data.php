<?php

/**
 * PayU Data Helper Model
 *
 * @copyright Copyright (c) 2011-2016 PayU
 * @license http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 */
class PayU_Account_Helper_Data extends Mage_Core_Helper_Abstract
{
    /**
     * Converts the Magento float values (for instance 9.9900) to PayU accepted Currency format (999)
     *
     * @param  string
     * @return int
     */
    public function toAmount($val)
    {
        $multiplied = $val * 100;
        $round = (int)round($multiplied);

        return $round;
    }
}
