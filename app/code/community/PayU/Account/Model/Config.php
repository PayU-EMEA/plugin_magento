<?php

/**
 * PayU Config Model
 *
 * @copyright Copyright (c) 2011-2016 PayU
 * @license http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 */
class PayU_Account_Model_Config
{
    /**
     * @var string self version
     */
    protected $_pluginVersion = '2.3.1';

    /**
     * @var string minimum Magento e-commerce version
     */
    protected $_minimumMageVersion = '1.6.0';

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * @var string
     */
    private $_method;

    /**
     * Constructor
     *
     * @param $params
     */
    public function __construct($params = array())
    {
        // assign current store id
        $this->setStoreId(Mage::app()->getStore()->getId());

        $this->_method = $params['method'];
    }

    /**
     * @param int $storeId
     * @return $this
     */
    public function setStoreId($storeId)
    {
        $this->_storeId = $storeId;
        return $this;
    }

    /** @return string get Merchant POS Id */
    public function getMerchantPosId()
    {
        return $this->getStoreConfig('pos_id');
    }

    /**
     * @return string get Signature Key
     */
    public function getSignatureKey()
    {
        return $this->getStoreConfig('signature_key');
    }

    /**
     * @return string get OAuth Client Id
     */
    public function getClientId()
    {
        return $this->getStoreConfig('oauth_client_id');
    }

    /**
     * @return string get OAuth Client Secret
     */
    public function getClientSecret()
    {
        return $this->getStoreConfig('oauth_client_secret');
    }

    /**
     * @return string get Sandbox
     */
    public function isSandbox()
    {
        return (bool)Mage::getStoreConfig('payment/' . $this->_method . '/sandbox', $this->_storeId);
    }

    /**
     * @param string $action
     * @param array $params
     *
     * @return string base module url
     */
    public function getUrl($action, $params = array())
    {
        $params['_secure'] = true;
        return Mage::getUrl('payu/payment/' . $action, $params);
    }

    /**
     * @return string get current plugin version
     */
    public function getPluginVersion()
    {
        return $this->_pluginVersion;
    }

    /**
     * @return string get minimum mage version for the plugin to work on
     */
    public function getMinimumMageVersion()
    {
        return $this->_minimumMageVersion;
    }

    /**
     * get Store Config variable
     * @param $name
     * @return string
     */
    protected function getStoreConfig($name)
    {
        return Mage::getStoreConfig('payment/' . $this->_method . '/' . ($this->isSandbox() ? 'sandbox_' : '') . $name, $this->_storeId);
    }
}
