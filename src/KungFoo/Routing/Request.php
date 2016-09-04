<?php
namespace KungFoo\Routing;
use KungFoo\Helpers\ServiceLocator;

class Request
{
	public $method;

	protected $path = '';
	private $secretKey;
	private $apikey;
	private $container;

	/**
	 * constructor receives the current path, as it has been interpreted from the higher app layer
	 *
	 * @param string         $path api/something/nice
	 * @param ServiceLocator $container
	 */
	public function __construct($path, ServiceLocator $container) {
		$this->path = explode('/',$path);
		$this->container = $container;

		$this->method    = strtolower($_SERVER['REQUEST_METHOD']);
		$this->secretKey = defined('API_SECRET_KEY') ? API_SECRET_KEY : '';
		$this->apikey    = defined('API_SHARED_KEY') ? API_SHARED_KEY : '';
	}

	public function setSignedCredentials($apisecret, $apikey) {
		$this->secretKey = $apisecret; // private secret used for signature
		$this->apikey    = $apikey; // shared secret
	}

	/**
	 * allow read access to the path
	 * @return array the path split by '/'
	 */
	public function getPath() {
		return $this->path;
	}

	/**
	 * requests should include:
	 *
	 * GET /api/v1/test/123
	 * X-AUTHORIZATION: 907c762e069589c2cd2a229cdae7b8778caa9f07 <-- API Access Key
	 * X-TIMESTAMPGMT: 23454567 <- GMT/UTC Timestamp
	 * X-UNIQUEID: x4sad131asddf123123sd <- unique request id
	 * X-SIGNATURE: asdjlaksdjkasdjlkasdjaskldjalsd <- hmac signatur
	 *
	 * 
	 * @param  array $requiredSignatureBodyParams array containing the names of all required provided parameters
	 * @param  array $requestData  data source
	 * @return boolean             valid or invalid
	 */
	public function checkSignature(array $requiredSignatureBodyParams = array(), array $requestData = array()) {

		$timestamp = isset($_SERVER['HTTP_X_TIMESTAMPGMT']) ? $_SERVER['HTTP_X_TIMESTAMPGMT'] : false;
		$apikey    = isset($_SERVER['HTTP_X_AUTHORIZATION']) ? $_SERVER['HTTP_X_AUTHORIZATION'] : false;
		$uniqueid  = isset($_SERVER['HTTP_X_UNIQUEID']) ? $_SERVER['HTTP_X_UNIQUEID'] : false;
		$signature = isset($_SERVER['HTTP_X_SIGNATURE']) ? $_SERVER['HTTP_X_SIGNATURE'] : false;
		$path      = $_SERVER['REQUEST_URI']; // we will sign the request uri without domain and protocol

		foreach ($requiredSignatureBodyParams as $key) {
			if (empty($requestData[$key])) {
				return false;
			}
		}

		$signedData = array();
		ksort($requestData);
		foreach ($requestData as $key => $value) {
			$signedData[$key] = $value;
		}
		$signedData['X_AUTHORIZATION'] = $apikey;
		$signedData['X_METHOD']        = $path;
		$signedData['X_TIMESTAMP']     = $timestamp;
		$signedData['X_UNIQUEID']      = $uniqueid;
		$signedString = \http_build_query($signedData);

		// we do not allow empty values.
		if (empty($timestamp) || empty($apikey) || empty($uniqueid) || empty($signature)) {

			return false;
		}

		if (
			strlen($timestamp) < 8 || strlen($timestamp) > 11 ||
			strlen($apikey) < 8 || strlen($apikey) > 256 ||
			strlen($uniqueid) < 8 || strlen($uniqueid) > 256 ||
			strlen($signature) < 8 || strlen($signature) > 4096 ||
			strlen($signedString) > 40960
		) {

			return false;
		}

		// check unique request id and log request
		try {
			if (!$this->checkIfUnique($uniqueid)) {
				return false;
			}
		} catch (\Exception $e) {
			return false;
		}

		$correctSignature = $this->getSignature($signedString);

		return $signature === $correctSignature;
	}

	/**
	 * send a signed post request
	 * @param  string $uri  target uri including protocol
	 * @param  array $data the data array
	 * @return string       the respose body
	 */
	public function signedPost($uri, $data) {
		$url = parse_url($uri);
		return $this->sendSignedRequest($uri, 'post', $url['path'], $data); // we will sign the request uri without domain and protocol
	}

	/**
	 * send a signed put request
	 * @param  string $uri  target uri including protocol
	 * @param  array $data the data array
	 * @return string       the respose body
	 */
	public function signedPut($uri, $data) {
		$url = parse_url($uri);
		return $this->sendSignedRequest($uri, 'put', $url['path'], $data); // we will sign the request uri without domain and protocol
	}

	/**
	 * Send a signed request to the given url using the given method
	 *
	 * We will create and sign the following data:
	 * - timestamp
	 * - apikey
	 * - uniqueid
	 * - method
	 * - all of the provided $data elements
	 *
	 * @param string $uri
	 * @param string $requestMethod
	 * @param string $calledFunction
	 * @param mixed $data
	 * @return mixed
	 */
	protected function sendSignedRequest($uri, $requestMethod, $calledFunction, $data) {
		// setup variables
		$requestMethod = strtolower($requestMethod);
		$timestamp     = strtotime(gmdate('Y-m-d H:i:s'));
		$uniqueid      = $timestamp.uniqid('',true); // generate a unique id

		// sign the whole request
		$signedData = array();
		ksort($data); // alphabetically sort this
		foreach ($data as $key => $value) {
			$signedData[$key] = $value;
		}
		// add the required parameters as well, overwrites possible $data keys with the same name!
		$signedData['X_AUTHORIZATION'] = $this->apikey;
		$signedData['X_METHOD']        = $calledFunction;
		$signedData['X_TIMESTAMP']     = $timestamp;
		$signedData['X_UNIQUEID']      = $uniqueid;
		$signedString = \http_build_query($signedData);

		// create the signature which will be appended to the headers
		$signature = $this->getSignature($signedString);
		$body = \http_build_query($data);

		$ch = curl_init();
		if (defined('DEVELOPMENT') && DEVELOPMENT === true) {
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);			
		}

		curl_setopt($ch, CURLOPT_URL, $uri);
		if ($requestMethod == 'post') {
			curl_setopt($ch, CURLOPT_POST, 1);
		} elseif ($requestMethod == 'put') {
	        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'PUT');
		}

		if (!empty($data)) {
			curl_setopt($ch, CURLOPT_POSTFIELDS, $body);  //Post Fields
		}
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true); // we want to receive a possible response body

		$headers = array();
		$headers[] = 'X-AUTHORIZATION: ' . $this->apikey;
		$headers[] = 'X-TIMESTAMPGMT: ' . $timestamp;
		$headers[] = 'X-UNIQUEID: ' . $uniqueid;
		$headers[] = 'X-SIGNATURE: ' . $signature;
		$headers[] = 'Cache-Control: no-cache';
		$headers[] = 'Content-Type: application/x-www-form-urlencoded; charset=utf-8';
		curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

		// excecute the curl call
		$server_output = curl_exec($ch);

		curl_close($ch);
		return $server_output;
	}

	/**
	 * check if the provided uniqueid has been provided previously or if it is a new one
	 *
	 * @param string $uniqueid
	 * @param string $apikey
	 * @return bool
	 * @throws \Exception
	 *
	 */
	private function checkIfUnique($uniqueid, $apikey = '') {
		// we want to access a "persistency layer" that will be able to store and retrieve values by key
		// this is a weak spot - you need to prepare your implementation of 'uniqueObjectsStore'!
		$store = $this->container->resolve('uniqueObjectsStore');
		if (!is_callable(array($store, 'has')) || !is_callable(array($store, 'store'))) {
			throw new \Exception('uniqueObjectsStore not available');
		}

		if ($store->has($uniqueid)) {
			return false;
		}
		$store->store($uniqueid, array('apikey'=>$apikey, 'remote'=>$_SERVER['REMOTE_ADDR']));
		return true;
	}

	/**
	 * produce a hmac signature using the requests secret key
	 * @param  string $signedString the string that should be signed
	 * @return string               the resulting hmac signature
	 */
	private function getSignature($signedString) {
		return base64_encode(hash_hmac('sha256', $signedString, $this->secretKey, true));
	}
}
