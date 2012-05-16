<?php

/*
	ver. 0.1.7
	OpenPayU Standard Library
	
	@copyright  Copyright (c) 2011-2012 PayU
	@license    http://opensource.org/licenses/LGPL-3.0  Open Software License (LGPL 3.0)
	http://www.payu.com
	http://twitter.com/openpayu
	
	
	CHANGE_LOG:
	2012-02-14 ver. 0.1.7
		- all accesors from OpenPayU_Configuration, OpenPayU_OAuthResult, OpenPayU_Result classes are changed into set/get
		- classes created in 0.1.x versions was distributed to different files under directory OpenPayU
		- added OpenPayU::addOutputConsole() function
	2012-01-27
		- fixed call verifyResponse funciton, OpenPayU::verifyResponse
		- fixed orderDomainRequest.orderStatusUpdateRequest.timestamp in OpenPayU::updateStatus function, added Timestamp
	2012-01-20, ver. 0.1.6
		- added OpenPayU_Configuration::getServiceUrl() function
		- added OpenPayU_Configuration::getSummaryUrl() function
		- added OpenPayU_Configuration::getAuthUrl() function
		- added OpenPayU_Configuration::getEnvironment() function
		- added OpenPayU_Configuration::getMerchantPosId() function
		- added OpenPayU_Configuration::getPosAuthKey() function
		- added OpenPayU_Configuration::getClientId() function
		- added getClientSecret() function
		- added OpenPayU_Configuration::getClientSecret() function
		- added OpenPayU_Configuration::getSignatureKey() function
		- changed assignment variables in OpenPayU_Configuration
	2012-01-18, ver. 0.1.5
		- added $result->sessionId in OpenPayU_Order::consumeShippingCostRetrieveRequest function
	2012-01-12, ver. 0.1.3
		- change the display message 'OpenPayUNetwork::$openPayuEndPointUrl is empty' 
		- optimization function OpenPayUNetwork::isCurlInstalled, removed else condition
		- removed $countryCode unused parameter of the buildOpenPayuForm function
		- qualifiers change
		- removed unused $url in OpenPayuOAuth::getAccessTokenByCode function
		- removed unused $url in OpenPayuOAuth::getAccessToken function
		- removed unused $url in OpenPayuOAuth::getAccessTokenByClientCredentials function
		- removed unused $url in OpenPayuOAuth::getAccessTokenOnly function
		- changed comparing to the empty string to the empty() in getOpenPayuEndPoint(), sendOpenPayuDocument(), sendOpenPayuDocumentAuth()
	 2012-01-11, ver. 0.1.3
		- OpenPayU_Configuration::environment accept 'custom' url service now.
	 2012-01-03, ver. 0.1.3
		- arguments in function environment is converted to lower char
	 2011-12-20, ver. 0.1.0
		 - added classes OpenPayU_Configuration, OpenPayU_Order, OpenPayU_OAuth
		 - added method verifyResponse
	2011-11-07, ver. 0.0.18
		- bugfix for document parsing errors
	2011-11-06, ver. 0.0.17
		- transfer of algorithm computing authentication header to SKD
	2011-11-04, ver. 0.0.16
		- changes connected with changing OrderUpdateRequest with OrderNotifyRequest.
	2011-09-09, ver. 0.0.15
		- added http header authentication
*/

include_once('openpayu_domain.php');

class OpenPayUNetwork
{
	/** @var string OpenPayU EndPoint Url */
	protected static $openPayuEndPointUrl = '';

	/**
	 * The function sets EndPointUrl param of OpenPayU
	 * @access public
	 * @param string $ep 
	 */
	public static function setOpenPayuEndPoint($ep) {
		OpenPayUNetwork::$openPayuEndPointUrl = $ep;
		return;
	}

	/**
	 * This function checks the availability of cURL
	 * @access private
	 * @return bool
	 */
	private static function isCurlInstalled() {
		if  (in_array  ('curl', get_loaded_extensions())) {
			return true;
		}

		return false;
	}

	/**
	 * The function returns the parameter EndPointUrl OpenPayU
	 * @access public
	 * @return string
	 */
	public static function getOpenPayuEndPoint() {
		if (empty(OpenPayUNetwork::$openPayuEndPointUrl)) {
			throw new Exception('OpenPayUNetwork::$openPayuEndPointUrl is empty');
		}

		return OpenPayUNetwork::$openPayuEndPointUrl;
	}
	
	/**
	 * This function sends data to the EndPointUrl OpenPayU
	 * @access public
	 * @param string $doc
	 * @return string $response
	 */
	public static function sendOpenPayuDocument($doc) {

		if (empty(OpenPayUNetwork::$openPayuEndPointUrl)) {
			throw new Exception('OpenPayUNetwork::$openPayuEndPointUrl is empty');
		}

		$response = '';
		$xml = urlencode($doc);
		if (OpenPayUNetwork::isCurlInstalled()) {
			$response = OpenPayU::sendData(OpenPayUNetwork::$openPayuEndPointUrl, 'DOCUMENT='.$xml);
		} else {
			throw new Exception('cURL is not available');
		}

		return $response;
	}

	/**
	 * This function sends auth data to the EndPointUrl OpenPayU
	 * @access public
	 * @param string $doc
	 * @param integer $merchantPosId
	 * @param string $signatureKey
	 * @param string $algorithm
	 * @return string $response
	 */
	public static function sendOpenPayuDocumentAuth($doc, $merchantPosId, $signatureKey, $algorithm = 'MD5')
	{
		if (empty(OpenPayUNetwork::$openPayuEndPointUrl)) {
			throw new Exception('OpenPayUNetwork::$openPayuEndPointUrl is empty');
		}

		if (empty($signatureKey)) {
			throw new Exception('Merchant Signature Key should not be null or empty.');
		}

		if (empty($merchantPosId)) {
			throw new Exception('MerchantPosId should not be null or empty.');
		}
		
		$tosigndata = $doc.$signatureKey;
		$xml = urlencode($doc);
		$signature = '';
		if($algorithm=='MD5'){
			$signature = md5($tosigndata);
		} else if($algorithm=='SHA'){
			$signature = sha1($tosigndata);
		} else if($algorithm=='SHA-256' || $algorithm=='SHA256' || $algorithm=='SHA_256'){
			$signature = hash('sha256',$tosigndata);
		}
		$authData = 'sender='.$merchantPosId.
					';signature='.$signature.
					';algorithm='.$algorithm.
					';content=DOCUMENT';
		$response = '';

		if (OpenPayUNetwork::isCurlInstalled()) {
			$response = OpenPayU::sendDataAuth( OpenPayUNetwork::$openPayuEndPointUrl, 'DOCUMENT='.$xml, $authData);
		} else {
			throw new Exception('curl is not available');
		}

		return $response;
	}
	
	/**
	 * This function sends auth data to the EndPointUrl OpenPayU
	 * @access public
	 * @param string $url
	 * @param string $doc
	 * @param string $authData
	 * @return string $response
	 */
	public static function sendDataAuth($url, $doc, $authData)
	{
		$ch = curl_init($url );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $doc);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		curl_setopt($ch,CURLOPT_HTTPHEADER,array('OpenPayu-Signature:'.$authData));

		$response = curl_exec($ch);

		return $response;
	}

	/**
	 * This function sends data to the EndPointUrl OpenPayU
	 * @access public
	 * @param string $url
	 * @param string $doc
	 * @return string $response
	 */
	public static function sendData($url, $doc)
	{
		$ch = curl_init($url );
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $doc);
		curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);

		$response = curl_exec($ch);

		return $response;
	}
}

class OpenPayUBase extends OpenPayUNetwork {

	/** @var string outputConsole message */
	protected static $outputConsole = '';

	/**
	 * Show outputConsole message
	 * @access public
	 */
	public static function printOutputConsole() {
		echo OpenPayU::$outputConsole;
	}
	
	/**
	 * Add $outputConsole message
	 * @access public
	 * @param string $header
	 * @param string $text
	 */
	public static function addOutputConsole($header, $text='') {
		OpenPayU::$outputConsole .= '<br/><strong>' . $header . ':</strong><br />' . $text . '<br/>';;
	}

	/**
	 * Function builds OpenPayU Request Document
	 * @access public
	 * @param string $data
	 * @param string $startElement Name of Document Element
	 * @param string $version Xml Version
	 * @param string $xml_encoding Xml Encoding
	 * @return string
	 */
	public static function buildOpenPayURequestDocument($data, $startElement, $version = '1.0', $xml_encoding = 'UTF-8') {
		return OpenPayUBase::buildOpenPayUDocument($data, $startElement, 1, $version, $xml_encoding);
	}
	
	/**
	 * Function builds OpenPayU Response Document
	 * @access public
	 * @param string $data
	 * @param string $startElement Name of Document Element
	 * @param string $version Xml Version
	 * @param string $xml_encoding Xml Encoding
	 * @return string
	 */
	public static function buildOpenPayUResponseDocument($data, $startElement, $version = '1.0', $xml_encoding = 'UTF-8') {
		return OpenPayUBase::buildOpenPayUDocument($data, $startElement, 0, $version, $xml_encoding);
	}
	
	/**
	 * Function converts array to XML document
	 * @access public
	 * @param string $xml
	 * @param string $data
	 * @param string $parent
	 */
	public static function arr2xml(XMLWriter $xml, $data, $parent) {
		foreach($data as $key => $value) {
			if (is_array($value)){
				if (is_numeric($key)) {
					OpenPayUBase::arr2xml($xml, $value, $key);
				} else {
					$xml->startElement($key);
					OpenPayUBase::arr2xml($xml, $value, $key);
					$xml->endElement();
				}
				continue;
			}
			$xml->writeElement($key, $value);
		}
	}
	
	/**
	 * Function converts array to Form
	 * @access public
	 * @param string $data
	 * @param string $parent
	 * @param integer $index
	 * @return string
	 */
	public static function arr2form($data, $parent, $index) {
		$fragment = '';
		foreach($data as $key => $value) {
			if (is_array($value)){
				if (is_numeric($key)) {
					$fragment .= OpenPayUBase::arr2form($value, $parent, $key);
				} else {
					$p = $parent != '' ? $parent . '.' . $key : $key;
					if (is_numeric($index)) {
						$p .= '[' . $index . ']';
					}
					$fragment .= OpenPayUBase::arr2form($value, $p, $key);
				}
				continue;
			}

			$path = $parent != '' ? $parent . '.' . $key : $key;
			$fragment .= OpenPayUBase::buildFormFragmentInput($path, $value);
		}

		return $fragment;
	}
	
	/**
	 * Function converts xml to array
	 * @access public
	 * @param string $xml
	 * @return array
	 */
	public static function read($xml) {
		$tree = null;
		while($xml->read()) {
			if($xml->nodeType == XMLReader::END_ELEMENT) {
				return $tree;
			}
				
			else if($xml->nodeType == XMLReader::ELEMENT) {
				if (!$xml->isEmptyElement)	{
					$tree[$xml->name] = OpenPayUBase::read($xml);
				}
			}
				
			else if($xml->nodeType == XMLReader::TEXT) {
				$tree = $xml->value;
			}
		}
		return $tree;
	}
	
	/**
	 * Function builds OpenPayU Xml Document
	 * @access public
	 * @param string $data
	 * @param string $startElement
	 * @param integer $request
	 * @param string $xml_version
	 * @param string $xml_encoding
	 * @return xml
	 */
	public static function buildOpenPayUDocument($data, $startElement, $request = 1, $xml_version = '1.0', $xml_encoding = 'UTF-8') {
		if(!is_array($data)){
			return false;
		}

		$xml = new XmlWriter();
		$xml->openMemory();
		$xml->startDocument($xml_version, $xml_encoding);
		$xml->startElementNS(null, 'OpenPayU', 'http://www.openpayu.com/openpayu.xsd');

		$header = $request == 1 ? 'HeaderRequest' : 'HeaderResponse';

		$xml->startElement($header);

		$xml->writeElement('Algorithm', 'MD5');

		$xml->writeElement('SenderName', 'exampleSenderName');
		$xml->writeElement('Version', $xml_version);

		$xml->endElement();

		// domain level - open
		$xml->startElement(OpenPayUDomain::getDomain4Message($startElement));

		// message level - open
		$xml->startElement($startElement);

		OpenPayUBase::arr2xml($xml, $data, $startElement);

		// message level - close
		$xml->endElement();
		// domain level - close
		$xml->endElement();
		// document level - close
		$xml->endElement();

		return $xml->outputMemory(true);
	}

	/**
	 * Function builds form input element
	 * @access public
	 * @param string $name
	 * @param string $value
	 * @param string $type
	 * @return string
	 */
	public static function buildFormFragmentInput($name, $value, $type = 'hidden') {
		return "<input type='$type' name='$name' value='$value'>\n";
	}
	
	/**
	 * Function builds OpenPayU Form
	 * @access public
	 * @param string $data
	 * @param string $msgName
	 * @param string $version
	 * @return string
	 */
	public static function buildOpenPayuForm($data, $msgName, $version= '1.0') {
		if(!is_array($data)) {
			return false;
		}

		$url = OpenPayUNetwork::getOpenPayuEndPoint();

		$form  = "<form method='post' action='" . $url . "'>\n";
		$form .= OpenPayUBase::buildFormFragmentInput('HeaderRequest.Version', $version);
		$form .= OpenPayUBase::buildFormFragmentInput('HeaderRequest.Name', $msgName);
		$form .= OpenPayUBase::arr2form($data, '', '');
		$form .= "</form>";

		return $form;
	}

	/**
	 * Function converts Xml string to array 
	 * @access public
	 * @param string $data
	 * @return array
	 */
	public static function parseOpenPayUDocument($xmldata) {

		$xml = new XMLReader();
		$xml->XML($xmldata);

		$assoc = OpenPayUBase::read($xml);

		return $assoc;
	}
}

class OpenPayU extends OpenPayUBase {

	/**
	 * Function builds OrderCreateRequest Document 
	 * @access public
	 * @param string $data
	 * @return string
	 */
	public static function buildOrderCreateRequest($data)
	{
		$xml = OpenPayU::buildOpenPayURequestDocument($data, 'OrderCreateRequest');
		return $xml;
	}
	
	/**
	 * Function builds OrderRetrieveRequest Document 
	 * @access public
	 * @param array $data
	 * @return xml
	 */
	public static function buildOrderRetrieveRequest($data)
	{
		$xml = OpenPayU::buildOpenPayURequestDocument($data, 'OrderRetrieveRequest');
		return $xml;
	}

	/**
	 * Function builds ShippingCostRetrieveResponse Document 
	 * @access public
	 * @param array $data
	 * @return xml
	 */
	public static function buildShippingCostRetrieveResponse($data, $reqId) {

		$cost = array (
			'ResId' =>  $reqId,
			'Status' => array('StatusCode' => 'OPENPAYU_SUCCESS'),
			'AvailableShippingCost' => $data
		);

		$xml = OpenPayU::buildOpenPayUResponseDocument($cost, 'ShippingCostRetrieveResponse');
		return $xml;
	}

	/**
	 * Function builds buildOrderNotifyResponse Document 
	 * @access public
	 * @param string $reqId
	 * @return xml
	 */
	public static function buildOrderNotifyResponse($reqId) {

		$cost = array (
			'ResId' =>  $reqId, 
			'Status' => array('StatusCode' => 'OPENPAYU_SUCCESS')
		);

		$xml = OpenPayU::buildOpenPayUResponseDocument($cost, 'OrderNotifyResponse');
		return $xml;
	}

	/**
	 * Function builds verifyResponse Status 
	 * @access public
	 * @param string $data
	 * @param string $message
	 * @return string $xml
	 */
	public static function verifyResponse($data, $message) {
			
		$arr = OpenPayU::parseOpenPayUDocument(stripslashes($data));
		$status_code = $arr['OpenPayU']['OrderDomainResponse'][$message]['Status'];
		if($status_code == null){
			$status_code = $arr['OpenPayU']['HeaderResponse']['Status'];
		}
		return $status_code;
	}

	/**
	 * Function returns OrderCancelResponse Status Document 
	 * @access public
	 * @param string $data
	 * @return string $xml
	 */
	public static function verifyOrderCancelResponseStatus($data) {
		return OpenPayU::verifyResponse($data, 'OrderCancelResponse');
	}
	
	/**
	 * Function returns OrderStatusUpdateResponse Status Document 
	 * @access public
	 * @param string $data
	 * @return string $xml
	 */
	public static function verifyOrderStatusUpdateResponseStatus($data) {
		return OpenPayU::verifyResponse($data, 'OrderStatusUpdateResponse');
	}
	
	/**
	 * Function returns OrderCreateResponse Status 
	 * @access public
	 * @param string $data
	 * @return string $status_code
	 */
	public static function verifyOrderCreateResponse($data) {
			
		$arr = OpenPayU::parseOpenPayUDocument(stripslashes($data));
		$status_code = $arr['OpenPayU']['OrderDomainResponse']['OrderCreateResponse']['Status'];
		if($status_code == null){
			$status_code = $arr['OpenPayU']['HeaderResponse']['Status'];
		}
		return $status_code;
	}

	/**
	 * Function returns OrderRetrieveResponse Status 
	 * @access public
	 * @param string $data
	 * @return string $status_code
	 */
	public static function verifyOrderRetrieveResponseStatus($data) {
			
		$arr = OpenPayU::parseOpenPayUDocument(stripslashes($data));
		$status_code = $arr['OpenPayU']['OrderDomainResponse']['OrderRetrieveResponse']['Status'];
		if($status_code == null){
			$status_code = $arr['OpenPayU']['HeaderResponse']['Status'];
		}
		return $status_code;
	}

	/**
	 * Function returns OrderRetrieveResponse Data 
	 * @access public
	 * @param string $data
	 * @return string $order_retrieve
	 */
	public static function getOrderRetrieveResponse($data)
	{
		$arr = OpenPayU::parseOpenPayUDocument(stripslashes($data));
		$order_retrieve = $arr['OpenPayU']['OrderDomainResponse']['OrderRetrieveResponse'];

		return $order_retrieve;
	}

	/**
	 * Function builds OrderCancelRequest Document 
	 * @access public
	 * @param string $data
	 * @return string $xml
	 */
	public static function buildOrderCancelRequest($data) {
		$xml = OpenPayU::buildOpenPayURequestDocument($data, 'OrderCancelRequest');
		return $xml;
	}
	
	/**
	 * Function builds OrderStatusUpdateRequest Document 
	 * @access public
	 * @param string $data
	 * @return string $xml
	 */
	public static function buildOrderStatusUpdateRequest($data) {
		$xml = OpenPayU::buildOpenPayURequestDocument($data, 'OrderStatusUpdateRequest');
		return $xml;
	}
}

class OpenPayuOAuth extends OpenPayUBase {

	public static function getAccessTokenByCode($code, $oauth_client_name, $oauth_client_secret, $page_redirect) {
		$params = 'code=' . $code . '&client_id=' . $oauth_client_name . '&client_secret=' . $oauth_client_secret . '&grant_type=authorization_code&redirect_uri=' . $page_redirect;

		$response = OpenPayU::sendData( OpenPayUNetwork::$openPayuEndPointUrl, $params);

		$resp_json =  json_decode($response);
		OpenPayU::addOutputConsole('oauth response', $response);
		$access_token = $resp_json->{"access_token"};

		if(empty($access_token)) {
			throw new Exception('access_token is empty, error: ' . $response);
		}

		return $resp_json;
	}

	public static function getAccessToken($code, $oauth_client_name, $oauth_client_secret, $page_redirect) {
		$params = 'code=' . $code . '&client_id=' . $oauth_client_name . '&client_secret=' . $oauth_client_secret . '&grant_type=authorization_code&redirect_uri=' . $page_redirect;

		$response = OpenPayU::sendData( OpenPayUNetwork::$openPayuEndPointUrl, $params);

		$resp_json =  json_decode($response);
		OpenPayU::addOutputConsole('oauth response', $response);
		$access_token = $resp_json->{"access_token"};

		if(empty($access_token)) {
			throw new Exception('access_token is empty, error: ' . $response);
		}

		return $access_token;
	}

	public static function getAccessTokenByClientCredentials($oauth_client_name, $oauth_client_secret)
	{
		$params = 'client_id=' . $oauth_client_name . '&client_secret=' . $oauth_client_secret . '&grant_type=client_credentials';

		$response = OpenPayU::sendData(OpenPayUNetwork::$openPayuEndPointUrl, $params);

		$resp_json =  json_decode($response);
		OpenPayU::addOutputConsole('oauth response', $response);

		$access_token = $resp_json->{'access_token'};

		if(empty($access_token)) {
			throw new Exception('access_token is empty, error: ' . $response);
		}

		return $resp_json;
	}

	public static function getAccessTokenOnly($oauth_client_name, $oauth_client_secret)
	{
		$params = 'client_id=' . $oauth_client_name . '&client_secret=' . $oauth_client_secret . '&grant_type=client_credentials';

		$response = OpenPayU::sendData(OpenPayUNetwork::$openPayuEndPointUrl, $params);

		$resp_json =  json_decode($response);
		OpenPayU::addOutputConsole('oauth response', $response);

		$access_token = $resp_json->{'access_token'};

		if(empty($access_token)) {
			throw new Exception('access_token is empty, error: ' . $response);
		}

		return $access_token;
	}
}

include_once('OpenPayU/Configuration.php');
include_once('OpenPayU/Result.php');
include_once('OpenPayU/Order.php');
include_once('OpenPayU/ResultOAuth.php');
include_once('OpenPayU/OAuth.php');

?>