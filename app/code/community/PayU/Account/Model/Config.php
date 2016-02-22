<?php

/**
 *    ver. 1.9.0
 *    PayU Config Model
 *
 * @copyright  Copyright (c) 2011-2014 PayU
 * @license    http://opensource.org/licenses/GPL-3.0  Open Software License (GPL 3.0)
 *    http://www.payu.com
 *    http://www.openpayu.com
 *    http://twitter.com/openpayu
 */
class PayU_Account_Model_Config
{
    /**
     * @var string self version
     */
    protected $_pluginVersion = '2.1.9';

    /**
     * @var string minimum Magento e-commerce version
     */
    protected $_minimumMageVersion = '1.6.0';

    /**
     * @var string latest version path in goods array
     */
    protected $_latestVersionPath = "plugins_magento_1.6.0";


    /**
     * @var array latest plugin version info
     */
    protected $_latestVersion = array(
        'lang'        => 'en',
        'version'     => '1.8.1',
        'description' => 'PayU Account is a web application designed as an e-wallet for shoppers willing to open an account, define their payment options, see their purchase history and manage personal profiles.',
        'docs'        => array(
            'guides'   => array(
                0 => array(
                    'name' => 'Implementation guide',
                    'url'  => 'http://developers.payu.com/en/',
                ),
                1 => array(
                    'name' => 'Quick guide',
                    'url'  => 'http://developers.payu.com/en/',
                ),
            ),
            'websites' => array(
                0 => array(
                    'name' => 'Information site',
                    'url'  => 'http://www.corporate.payu.com/',
                ),
            ),
        ),
    );

    /**
     * @var array The goods resources are stored here
     */
    protected $_goods = array(
        'lang'              => 'en',
        'media'             => array(
            'adverts' => array(
                'billboard'        => array(
                    1 => 'https://static.payu.com/en/standard/partners/ad/billboard/billboard750_100_1.jpg',
                    2 => 'https://static.payu.com/en/standard/partners/ad/billboard/billboard750_100_2.jpg',
                    3 => 'https://static.payu.com/en/standard/partners/ad/billboard/billboard750_100_3.jpg',
                ),
                'double_billboard' => array(
                    1 => 'https://static.payu.com/en/standard/partners/ad/billboard/double_billboard750_200_1.jpg',
                    2 => 'https://static.payu.com/en/standard/partners/ad/billboard/double_billboard750_200_2.jpg',
                    3 => 'https://static.payu.com/en/standard/partners/ad/billboard/double_billboard750_200_3.jpg',
                ),
                'skyscraper'       => array(
                    1 => 'https://static.payu.com/en/standard/partners/ad/skyscraper/skyscraper160_600_1.jpg',
                    2 => 'https://static.payu.com/en/standard/partners/ad/skyscraper/skyscraper160_200_1.jpg',
                    3 => 'https://static.payu.com/en/standard/partners/ad/skyscraper/skyscraper160_200_2.jpg',
                    4 => 'https://static.payu.com/en/standard/partners/ad/skyscraper/skyscraper160_200_3.jpg',
                ),
            ),
            'buttons' => array(
                1  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_01.png',
                2  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_02.png',
                3  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_03.png',
                4  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_04.png',
                5  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_long_01.png',
                6  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_long_02.png',
                7  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_long_03.png',
                8  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_long_04.png',
                9  => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_small_01.png',
                10 => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_small_02.png',
                11 => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_small_03.png',
                12 => 'https://static.payu.com/en/standard/partners/buttons/payu_account_button_small_04.png',
            ),
            'logos'   => array(
                1 => 'https://static.payu.com/en/standard/partners/logos/payu-account-logo-trans.png',
            ),
        ),
        'plugins'           => array(
            'osc'        => array(
                '2.3.1' => array(
                    'version'    => '1.0.5',
                    'info'       => 'http://www.openpayu.com/en/goods/plugin/osc/231/105/json/',
                    'repository' => 'github.com/payu/plugin_osc_231',
                ),
            ),
            'magento'    => array(
                '1.6.0' => array(
                    'version'    => '1.8.1',
                    'info'       => 'http://www.openpayu.com/en/goods/plugin/magento/160/181/json/',
                    'repository' => 'github.com/PayU/plugin_magento_160',
                ),
            ),
            'opencart'   => array(
                '1.5.3' => array(
                    'version'    => '0.1.5',
                    'info'       => 'http://www.openpayu.com/en/goods/plugin/opencart/153/015/json/',
                    'repository' => 'github.com/payu/plugin_opencart_153',
                ),
            ),
            'prestashop' => array(
                '1.4.4' => array(
                    'version'    => '1.9.9',
                    'info'       => 'http://www.openpayu.com/en/goods/plugin/prestashop/144/199/json/',
                    'repository' => 'github.com/payu/plugin_prestashop_144',
                ),
            ),
        ),
        'business_partners' => array(
            'payu_pl'          => array(
                'name' => 'PayU Poland - PayU',
                'type' => 'platnosci',
            ),
            'payu_ro_epayment' => array(
                'name'    => 'PayU Romania - ePayment',
                'type'    => 'epayment',
                'lu_url'  => 'https://secure.epayment.ro/order/lu.php',
                'idn_url' => 'https://secure.epayment.ro/order/idn.php',
                'irn_url' => 'https://secure.epayment.ro/order/irn.php',
            ),
            'payu_ru_epayment' => array(
                'name'    => 'PayU Russia - ePayment',
                'type'    => 'epayment',
                'lu_url'  => 'https://secure.payu.ru/order/lu.php',
                'idn_url' => 'https://secure.payu.ru/order/idn.php',
                'irn_url' => 'https://secure.payu.ru/order/irn.php',
            ),
            'payu_ua_epayment' => array(
                'name'    => 'PayU Ukraine - ePayment',
                'type'    => 'epayment',
                'lu_url'  => 'https://secure.payu.ua/order/lu.php',
                'idn_url' => 'https://secure.payu.ua/order/idn.php',
                'irn_url' => 'https://secure.payu.ua/order/irn.php',
            ),
            'payu_tr_epayment' => array(
                'name'    => 'PayU Turkey - ePayment',
                'type'    => 'epayment',
                'lu_url'  => 'https://secure.payu.com.tr/order/lu.php',
                'idn_url' => 'https://secure.payu.com.tr/order/idn.php',
                'irn_url' => 'https://secure.payu.com.tr/order/irn.php',
            ),
            'payu_hu_epayment' => array(
                'name'    => 'PayU Hungary - ePayment',
                'type'    => 'epayment',
                'lu_url'  => 'https://secure.payu.hu/order/lu.php',
                'idn_url' => 'https://secure.payu.hu/order/idn.php',
                'irn_url' => 'https://secure.payu.hu/order/irn.php',
            ),
        ),
    );

    /**
     * @var int
     */
    protected $_storeId;

    /**
     * Constructor
     *
     * @param $params
     */
    public function __construct($params = array())
    {
        // assign current store id
        $this->setStoreId(Mage::app()->getStore()->getId());
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

    /**
     * @return string get current environment
     */
    public function getEnvironment()
    {
        return PayU_Account_Model_System_Config_Source_Environment::PRODUCTION;
    }

    /** @return string get Merchant POS Id */
    public function getMerchantPosId()
    {
        // return Mage::getStoreConfig('payment/payu/'.$this->_prefix.'pos_id',Mage::app()->getStore()->getId());
        // currently it is the same, so no need to separate parameter configuration
        return $this->getClientId();
    }

    /**
     * @return string get Pos Auth Key
     */
    public function getPosAuthKey()
    {
        return $this->getStoreConfig('payment/payu_account/pos_auth_key');
    }

    /**
     * @return string get (OAuth Client Name)
     */
    public function getClientId()
    {
        return $this->getStoreConfig('payment/payu_account/oauth_client_name');
    }

    /**
     * @return string get (OAuth Client Secret)
     */
    public function getClientSecret()
    {
        return $this->getStoreConfig('payment/payu_account/oauth_client_secret');
    }

    /**
     * @return string get signature key
     */
    public function getSignatureKey()
    {
        return $this->getStoreConfig('payment/payu_account/signature_key');
    }

    /**
     * @return int order validity time in minutes
     */
    public function getOrderValidityTime()
    {
        $validityTime = $this->getStoreConfig('payment/payu_account/validity_time');
        if ($validityTime) {
            return $validityTime;
        }
        return "86400";
    }

    /**
     * @return string small logo src
     */
    public function getThumbnailSrc()
    {
        return $this->getStoreConfig('payment/payu_account/payuthumbnail');
    }

    /**
     * @return string advertisement banner url
     */
    public function getAdvertisementSrc()
    {
        return $this->localize($this->getStoreConfig('payment/payu_account/payuadvertisement'));
    }

    /**
     * @return string one step checkout button url
     */
    public function getButtonSrc()
    {
        return $this->localize($this->getStoreConfig('payment/payu_account/payubutton'));
    }

    /**
     * @return string base module url
     */
    public function getUrl($action)
    {
        return Mage::getUrl("payu_account/payment/$action", array('_secure' => true));
    }

    /**
     * @return string check if is one step checkout method enabled
     */
    public function getIsOneStepCheckoutEnabled()
    {
        return $this->getStoreConfig('payment/payu_account/onestepcheckoutenabled');
    }

    /**
     * @return int what is the default new order status
     */
    public function getNewOrderStatus()
    {
        return $this->getStoreConfig('payment/payu_account/order_status');
    }

    /**
     * @return bool
     */
    public function isLatestVersionInstalled()
    {
        return !Mage::helper('payu_account')->isFirstVersionNewer($this->_latestVersion['version'], $this->_pluginVersion);
    }

    /**
     * Returns goods array (or string when leaf selected) depending on the needs
     * @param string $name
     * @return array|string $goods
     */
    public function getGoods($name = null)
    {
        $goodsItem = $this->_goods;

        if ($name !== null) {

            $data = explode("_", $name);

            foreach ($data as $col) {
                if (empty($goodsItem[$col])) {
                    return array('error');
                }
                $goodsItem = $goodsItem[$col];
            }
        }
        return $this->localize($goodsItem);
    }

    /**
     * @return array get latest plugin version data
     */
    public function getLatestVersion()
    {
        return $this->_latestVersion;
    }

    /**
     * @return array get main latest version of plugin info
     */
    public function getLatestVersionInfo()
    {
        return $this->getGoods($this->_latestVersionPath, "_");
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
     * Change locale of given string
     *
     * @param $string
     * @param $s
     * @return string
     */
    protected function localize($string, $s = "/")
    {
        return Mage::helper('payu_account')->localize($string, $s);
    }

    /**
     * get Store Config variable
     * @param $name
     * @return string
     */
    protected function getStoreConfig($name)
    {
        return Mage::getStoreConfig($name, $this->_storeId);
    }

    /**
     * @return mixed
     */
    public function getDomainName()
    {
        return $_SERVER['HTTP_HOST'];
    }
}
