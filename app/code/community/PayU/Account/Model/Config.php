<?php

class PayU_Account_Model_Config
{
    /**
     * Sanbox code
     */
    const ENVIRONMENT_SANBOX = 'sandbox';

    /**
     * Secure code
     */
    const ENVIRONMENT_SECURE = 'secure';

    /**
     * Plugin version
     */
    const PLUGIN_VERSION = '2.4.4';

    /**
     * @var int
     */
    private $storeId;

    /**
     * @var string
     */
    private $method;

    /**
     * Constructor
     *
     * @param array $params
     * @param int|null $storeId
     * @throws Mage_Core_Model_Store_Exception
     */
    public function __construct($params = array(), $storeId = null)
    {
        if ($storeId !== null) {
            $this->storeId = $storeId;
        } else {
            $this->storeId = Mage::app()->getStore()->getId();
        }

        $this->method = $params['method'];
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
     * @return string
     */
    public function getPluginVersion()
    {
        return self::PLUGIN_VERSION;
    }

    /**
     * Initialize PayU configuration
     */
    public function initializeOpenPayUConfiguration()
    {
        OpenPayU_Configuration::setEnvironment($this->getEnvironment());
        OpenPayU_Configuration::setMerchantPosId($this->getStoreConfig('pos_id'));
        OpenPayU_Configuration::setSignatureKey($this->getStoreConfig('signature_key'));
        OpenPayU_Configuration::setOauthClientId($this->getStoreConfig('oauth_client_id'));
        OpenPayU_Configuration::setOauthClientSecret($this->getStoreConfig('oauth_client_secret'));
        OpenPayU_Configuration::setOauthTokenCache(new OauthCacheFile(Mage::getBaseDir('cache')));
        OpenPayU_Configuration::setSender('Magento ver ' . Mage::getVersion() . '/Plugin ver ' . $this->getPluginVersion());
    }

    /**
     * @return string
     */
    public function getMerchantPosId()
    {
        return \OpenPayU_Configuration::getMerchantPosId();
    }

    /**
     * @return bool
     */
    public function isShowPaytypes()
    {
        return (bool)Mage::getStoreConfig('payment/' . $this->method . '/paytypes', $this->storeId);
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return (bool)Mage::getStoreConfig('payment/' . $this->method . '/active', $this->storeId);
    }

    /**
     * @return array
     */
    public function getPaytypesOrder() {
        return explode(
            ',',
            str_replace(
                ' ',
                '',
                Mage::getStoreConfig('payment/' . $this->method . '/paytypes_order', $this->storeId)
            )
        );
    }

    /**
     * @return string
     */
    private function getEnvironment() {
        return $this->isSandbox() ? self::ENVIRONMENT_SANBOX : self::ENVIRONMENT_SECURE;
    }

    /**
     * get Store Config variable
     * @param $name
     * @return string
     */
    private function getStoreConfig($name)
    {
        return Mage::getStoreConfig('payment/' . $this->method . '/' . ($this->isSandbox() ? 'sandbox_' : '') . $name, $this->storeId);
    }

    /**
     * @return bool
     */
    private function isSandbox()
    {
        return (bool)Mage::getStoreConfig('payment/' . $this->method . '/sandbox', $this->storeId);
    }

}
