<?php

/**
*	ver. 0.1.6.5.1
*	PayU Config Model
*	
*	@copyright  Copyright (c) 2011-2012 PayU
*	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
 *	http://www.payu.com
 *	http://www.openpayu.com
 *	http://twitter.com/openpayu
*/

class PayU_Account_Model_Config
{
	/** @var string self version */
	protected $_pluginVersion = '0.1.6.5.1';
	
	/** @var string minimum Magento e-commerce version */
	protected $_minimumMageVersion = '1.6.0';
	
	/** @var string stores information about current working environment */
	protected $_env;
	
	/** @var string prefix for configuration variables depending on environment */
	protected $_prefix;
	
	/** @var string represents the goods url */
	protected $_goodsUrl = "http://openpayu.com/en/goods/json";
	
	/** @var latest version path in goods array */
	protected $_latestVersionPath = "plugins_magento_1.6.0";
	
	/** @var string latest version url */
	protected $_latestVersionUrl;
	
	/** @var array latest plugin version info */
	protected $_latestVersion;
	
	/** @var array The goods resources are stored here */
	protected $_goods;
	
	protected $_storeId;
	
	/**
	 * Constructor
	 *
	 * @param $params
	 */
    public function __construct($params = array())
    {

    	// assign current store id
    	$this->setStoreId();
    	// initialize the working environment    	
    	$this->setEnvironment();
    	// assign goods data
      	$this->setGoods();
      	// set latest version url
      	$this->setLatestVersionUrl();
      	// set latest version data
      	$this->setLatestVersion();
    }  
    

    
    /** set current store Id */
    protected function setStoreId(){
    	$this->_storeId = Mage::app()->getStore()->getId();
    }
    
    /**
     * Set the environment
     * @return string PayU environment variable
     */
    protected function setEnvironment(){
    	
    	switch(Mage::getStoreConfig('payment/payu_account/environment')){
		
			//check if we work in production mode
			case PayU_Account_Model_Environment::PRODUCTION:
				$this->_env = "secure";
				$this->_prefix = "";
				break;
			// default sandbox mode
			default:
				$this->_env = "sandbox";
				$this->_prefix = $this->_env."_";
				break;
				
		}
		
		return $this->_env;
    	
    }
    
    /** @return string get current environment */
    public function getEnvironment(){
    	return $this->setEnvironment();
    }
    
    /** @return string get Merchant POS Id */
    public function getMerchantPosId(){
    	
    	// return Mage::getStoreConfig('payment/payu/'.$this->_prefix.'pos_id',Mage::app()->getStore()->getId());
    	// currently it is the same, so no need to separate parameter configuration
    	return $this->getClientId();
    	
    }
    
    /** @return string get Pos Auth Key */
	public function getPosAuthKey(){
    	return $this->getStoreConfig('payment/payu_account/'.$this->_prefix.'pos_auth_key');
    }
    
    /** @return string get (OAuth Client Name) */
	public function getClientId(){
		return $this->getStoreConfig('payment/payu_account/'.$this->_prefix.'oauth_client_name');
    }
    
    /** @return string get (OAuth Client Secret) */
	public function getClientSecret(){
		return $this->getStoreConfig('payment/payu_account/'.$this->_prefix.'oauth_client_secret');
    }
    
    /** @return string get signature key */
	public function getSignatureKey(){
		return $this->getStoreConfig('payment/payu_account/'.$this->_prefix.'signature_key');
    }
    
    /** @return int order validity time in minutes */
	public function getOrderValidityTime(){
		
		$validityTime = $this->getStoreConfig('payment/payu_account/validity_time');
		
		if($validityTime)
    		$validityTime;
    	return "1440";
    }
    
    /** @return string small logo src */
    public function getThumbnailSrc(){
    	return $this->getStoreConfig('payment/payu_account/payuthumbnail');
    }
    
    /** @return string advertisement banner url */
	public function getAdvertisementSrc(){
        return $this->localize($this->getStoreConfig('payment/payu_account/payuadvertisement'));
    }
    
	/** @return string one step checkout button url */
	public function getButtonSrc(){
		return $this->localize($this->getStoreConfig('payment/payu_account/payubutton'));
    }
    
    /** $return string base module url */
    public function getBaseUrl(){
    	return Mage::getBaseUrl().'payu_account/payment/';
    }
    
    /** @return string check if is one step checkout method enabled */
	public function getIsOneStepCheckoutEnabled(){
		return $this->getStoreConfig('payment/payu_account/onestepcheckoutenabled');
    }
    
    /** @return string check if is one step checkout method available */
	public function getIsOneStepCheckoutAvailable(){
    	return true;
    }
    
    /** @return int what is the default new order status */
	public function getNewOrderStatus(){
		return $this->getStoreConfig('payment/payu_account/order_status');
    }
    
    public function isLatestVersionInstalled(){
    	return !Mage::helper('payu_account')->isFirstVersionNewer($this->_latestVersion['version'],$this->_pluginVersion);
    }
    
	/** @return int what is the default new order status */
	public function getIsSelfReturnEnabled(){
		return $this->getStoreConfig('payment/payu_account/selfreturn');
    }

    /**
     * Returns goods array (or string when leaf selected) depending on the needs
     * @param string $name
     * @return array|string $goods
     */
    public function getGoods($name = null){
    
    	$goodsItem = $this->_goods;
    	
    	if($name !== null){
    	
    		$name_arr = explode("_",$name);
    	
	    	foreach($name_arr as $col){
	    		if(empty($goodsItem[$col]))
	    			return array('error');
	    		$goodsItem = $goodsItem[$col];
	    	}
    	
    	}
    	
    	return $this->localize($goodsItem);
    	
    }
    
    /** @return array get latest plugin version data */
    public function getLatestVersion(){
    	return $this->_latestVersion;
    }
    
    /** @return array get main latest version of plugin info */
	public function getLatestVersionInfo(){
    	return $this->getGoods($this->_latestVersionPath,"_");
    }
    
    /** @return get current plugin version */
    public function getPluginVersion(){
    	return $this->_pluginVersion;
    }
    
    /** @return string get minimum mage version for the plugin to work on */
    public function getMinimumMageVersion(){
    	return $this->_minimumMageVersion;
    }
    
    /** assign latest version */
    protected function setLatestVersionUrl(){
    	$this->_latestVersionUrl = $this->getGoods($this->localize($this->_latestVersionPath."_info","_"));
    }
    
    /**
     * Change locale of given string
     * 
     * @param $string
     * @param $s
     * @return string
     */
    protected function localize($string, $s = "/"){
    	return Mage::helper('payu_account')->localize($string, $s);
    }
    
    /** assign latest version data */
	protected function setLatestVersion(){
    	$this->_latestVersion = $this->getArrayFromJsonResponse($this->_latestVersionUrl);
    }
    
    /** assign goods resources array */
    protected function setGoods(){
    	$this->_goods = $this->getArrayFromJsonResponse($this->_goodsUrl);
    }
    
    /**
     * Converts json response to array
     * 
     * @param $url string
     * @return array
     */
    protected function getArrayFromJsonResponse($url){
    	$httpClient = new Varien_Http_Client($url);
    	$response = $httpClient->request(Varien_Http_Client::GET);
    	return json_decode($response->getBody(),true);
    }
    
    /**
     * get Store Config variable
     * @param $name
     * @return string
     */
    protected function getStoreConfig($name){
    	return Mage::getStoreConfig($name,$this->_storeId);
    }
    
	public function getDomainName() 
	{ 
	    return $_SERVER['HTTP_HOST'];
	}
    
      
}
  