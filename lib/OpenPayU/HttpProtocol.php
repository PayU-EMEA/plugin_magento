<?php
/**
 * @copyright  Copyright (c) 2014 PayU
 * @license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *
 * http://www.payu.com
 * http://developers.payu.com
 * http://twitter.com/openpayu
 *
 */

/**
 * Interface OpenPayU_HttpProtocol
 */
interface OpenPayU_HttpProtocol
{
    public static function doRequest($requestType, $pathUrl, $fieldsList, $posId, $signatureKey);
}
