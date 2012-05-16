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
		- file created
		- all access to attributes of *Result classt is by public accessor
		- replaced $debug = 1 notation to $debug = TRUE
	
*/	

class OpenPayU_Order extends OpenPayU {


	public static function create($order, $debug = TRUE) {

		/*
		 openpayu data model
		 OrderCreateRequest : http://www.payu.com/openpayu/OrderDomainRequest.html#Link2
		 OrderCreateResponse : http://www.payu.com/openpayu/OrderDomainResponse.html#Link2
		 */

		// preparing payu service for order initialization
		$OrderCreateRequestUrl = OpenPayU_Configuration::getServiceUrl() . 'co/openpayu/OrderCreateRequest';
		if ($debug) {
			OpenPayU::addOutputConsole('OpenPayU endpoint for OrderCreateRequest message', $OrderCreateRequestUrl);
		}
		OpenPayU::setOpenPayuEndPoint($OrderCreateRequestUrl);

		// convert array to openpayu document
		$xml = OpenPayU::buildOrderCreateRequest($order);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderCreateRequest message', htmlentities($xml));
		}
		$merchantPosId = OpenPayU_Configuration::getMerchantPosId();
		$signatureKey = OpenPayU_Configuration::getSignatureKey();

		// send openpayu document with order initialization structure to PayU service
		$response = OpenPayU::sendOpenPayuDocumentAuth($xml, $merchantPosId, $signatureKey);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderCreateRequest message', htmlentities($response));
		}

		// verify response from PayU service
		$status = OpenPayU::verifyOrderCreateResponse($response);

		if ($debug) {
			OpenPayU::addOutputConsole('OrderCreateResponse status', serialize($status));
		}

		$result = new OpenPayU_Result();
		$result->setStatus($status);
		$result->setError($status['StatusCode']);
		$result->setSuccess($status['StatusCode'] == 'OPENPAYU_SUCCESS' ? TRUE : FALSE);
		$result->setRequest($order);
		$result->setResponse(OpenPayU::parseOpenPayUDocument($response));

		return $result;
	}

	public static function retrieve($sessionId, $debug = TRUE) {
		$req = array (
			'ReqId' => md5(rand()),
			'MerchantPosId' => OpenPayU_Configuration::getMerchantPosId(),
			'SessionId' => $sessionId
		);

		$OrderRetrieveRequestUrl = OpenPayU_Configuration::getServiceUrl() . 'co/openpayu/OrderRetrieveRequest';
		if ($debug) {
			OpenPayU::addOutputConsole('OpenPayU endpoint for OrderRetrieveRequest message', $OrderRetrieveRequestUrl);
		}

		$oauthResult = OpenPayu_OAuth::accessTokenByClientCredentials();

		OpenPayU::setOpenPayuEndPoint($OrderRetrieveRequestUrl . '?oauth_token=' . $oauthResult->getAccessToken());
		$xml = OpenPayU::buildOrderRetrieveRequest($req);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderRetrieveRequest message', htmlentities($xml));
		}

		$merchantPosId = OpenPayU_Configuration::getMerchantPosId();
		$signatureKey = OpenPayU_Configuration::getSignatureKey();
		$response = OpenPayU::sendOpenPayuDocumentAuth($xml, $merchantPosId, $signatureKey);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderRetrieveResponse message', htmlentities($response));
		}

		$status = OpenPayU::verifyOrderCreateResponse($response);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderRetrieveResponse status', serialize($status));
		}

		$result = new OpenPayU_Result();
		$result->setStatus($status);
		$result->setError($status['StatusCode']);
		$result->setSuccess($status['StatusCode'] == 'OPENPAYU_SUCCESS' ? TRUE : FALSE);
		$result->setRequest($order);

		try {
			$assoc = OpenPayU::parseOpenPayUDocument($response);
			$result->setResponse($assoc);
		} catch(Exception $ex) {
			if ($debug) {
				OpenPayU::addOutputConsole('OrderRetrieveResponse parse result exception', $ex->getMessage());
			}
		}

		return $result;
	}

	public static function consumeMessage($xml, $debug = TRUE) {
		$xml = stripslashes(urldecode($xml));
		$rq = OpenPayU::parseOpenPayUDocument($xml);

		$msg = $rq['OpenPayU']['OrderDomainRequest'];

		switch (key($msg)) {
			case 'OrderNotifyRequest':
				return OpenPayU_Order::consumeNotification($xml);
				break;
			case 'ShippingCostRetrieveRequest':
				return OpenPayU_Order::consumeShippingCostRetrieveRequest($xml);
				break;
			default:
				return key($smg);
				break;
		}
	}

	private static function consumeNotification($xml, $debug = TRUE) {
		if ($debug) {
			OpenPayU::addOutputConsole('OrderNotifyRequest message', $xml);
		}

		$xml = stripslashes(urldecode($xml));
		$rq = OpenPayU::parseOpenPayUDocument($xml);
		$reqId = $rq['OpenPayU']['OrderDomainRequest']['OrderNotifyRequest']['ReqId'];
		$sessionId = $rq['OpenPayU']['OrderDomainRequest']['OrderNotifyRequest']['SessionId'];
			
		if ($debug) {
			OpenPayU::addOutputConsole('OrderNotifyRequest data, reqId', $reqId . ', sessionId: ' . $sessionId);
		}

		// response to payu service
		$rsp = OpenPayU::buildOrderNotifyResponse($reqId);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderNotifyResponse message', $rsp);
		}
		header("Content-Type:text/xml");
		echo $rsp;


		$result = new OpenPayU_Result();
		$result->setSessionId($sessionId);
		$result->setSuccess(TRUE);
		$result->setRequest($rq);
		$result->setResponse($rsp);
		$result->setMessage('OrderNotifyRequest');		
		
		// if everything is alright return full data sent from payu service to client
		return $result;
	}

	private static function consumeShippingCostRetrieveRequest($xml, $debug = TRUE) {
		if ($debug) {
			OpenPayU::addOutputConsole('consumeShippingCostRetrieveRequest message', $xml);
		}

		$rq = OpenPayU::parseOpenPayUDocument($xml);

		$result = new OpenPayU_Result();
		$result->setCountryCode($rq['OpenPayU']['OrderDomainRequest']['ShippingCostRetrieveRequest']['CountryCode']);
		$result->setSessionId($rq['OpenPayU']['OrderDomainRequest']['ShippingCostRetrieveRequest']['SessionId']);
		$result->setReqId($rq['OpenPayU']['OrderDomainRequest']['ShippingCostRetrieveRequest']['ReqId']);
		$result->setMessage('ShippingCostRetrieveRequest');

		if ($debug) {
			OpenPayU::addOutputConsole('consumeShippingCostRetrieveRequest reqId', $result->getReqId() . ', countryCode: ' . $result->getCountryCode());
		}

		return $result;
	}

	public static function cancel($sessionId, $debug = TRUE) {

		$rq = array (
			'ReqId' => md5(rand()),
			'MerchantPosId' => OpenPayU_Configuration::getMerchantPosId(),
			'SessionId' => $sessionId
		);

		$result = new OpenPayU_Result();
		$result->setRequest($rq);

		$url = OpenPayU_Configuration::getServiceUrl() . 'co/openpayu/OrderCancelRequest';
		if ($debug) {
			OpenPayU::addOutputConsole('OpenPayU endpoint for OrderCancelRequest message',  $url);
		}

		$oauthResult = OpenPayu_OAuth::accessTokenByClientCredentials();
		OpenPayU::setOpenPayuEndPoint($url . '?oauth_token=' . $oauthResult->getAccessToken());

		$xml = OpenPayU::buildOrderCancelRequest($rq);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderCancelRequest message',  htmlentities($xml));
		}

		$merchantPosId = OpenPayU_Configuration::getMerchantPosId();
		$signatureKey = OpenPayU_Configuration::getSignatureKey();
		$response = OpenPayU::sendOpenPayuDocumentAuth($xml, $merchantPosId, $signatureKey);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderCancelResponse message',  htmlentities($response));
		}

		// verify response from PayU service
		$status = OpenPayU::verifyOrderCancelResponseStatus($response);

		if ($debug) {
			OpenPayU::addOutputConsole('OrderCancelResponse status', serialize($status));
		}

		$result->setStatus($status);
		$result->setError($status['StatusCode']);
		$result->setSuccess($status['StatusCode'] == 'OPENPAYU_SUCCESS' ? TRUE : FALSE);
		$result->setResponse(OpenPayU::parseOpenPayUDocument($response));

		return $result;
	}

	public static function updateStatus($sessionId, $status, $debug = TRUE) {

		$rq = array (
			'ReqId' => md5(rand()),
			'MerchantPosId' => OpenPayU_Configuration::getMerchantPosId(),
			'SessionId' => $sessionId,
			'OrderStatus' => $status,
			'Timestamp' => date('c')
		);

		$result = new OpenPayU_Result();
		$result->setRequest($rq);

		$url = OpenPayU_Configuration::getServiceUrl() . 'co/openpayu/OrderStatusUpdateRequest';
		if ($debug) {
			OpenPayU::addOutputConsole('OpenPayU endpoint for OrderStatusUpdateRequest message', $url);
		}

		$oauthResult = OpenPayu_OAuth::accessTokenByClientCredentials();
		OpenPayU::setOpenPayuEndPoint($url . '?oauth_token=' . $oauthResult->getAccessToken());

		$xml = OpenPayU::buildOrderStatusUpdateRequest($rq);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderStatusUpdateRequest message', htmlentities($xml));
		}

		$merchantPosId = OpenPayU_Configuration::getMerchantPosId();
		$signatureKey = OpenPayU_Configuration::getSignatureKey();
		$response = OpenPayU::sendOpenPayuDocumentAuth($xml, $merchantPosId, $signatureKey);
		if ($debug) {
			OpenPayU::addOutputConsole('OrderStatusUpdateResponse message', htmlentities($response));
		}

		// verify response from PayU service
		$status = OpenPayU::verifyOrderStatusUpdateResponseStatus($response);

		if ($debug) {
			OpenPayU::addOutputConsole('OrderStatusUpdateResponse status', serialize($status));
		}

		$result->setStatus($status);
		$result->setError($status['StatusCode']);
		$result->setSuccess($status['StatusCode'] == 'OPENPAYU_SUCCESS' ? TRUE : FALSE);
		$result->setResponse(OpenPayU::parseOpenPayUDocument($response));

		return $result;
	}
}

?>