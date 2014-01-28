<?php
class itunesReceiptValidator {

    const SANDBOX_URL    = 'https://sandbox.itunes.apple.com/verifyReceipt';
    const PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

    function __construct($endpoint, $receipt = NULL) {
        $this->setEndPoint($endpoint);

        if ($receipt) {
            $this->setReceipt($receipt);
        }
    }

    function getReceipt() {
        return $this->receipt;
    }

    function setReceipt($receipt) {
        if (strpos($receipt, '{') !== false) {
            $this->receipt = base64_encode($receipt);
        } else {
            $this->receipt = $receipt;
        }
    }

    function getEndpoint() {
        return $this->endpoint;
    }

    function setEndPoint($endpoint) {
        $this->endpoint = $endpoint;
    }

    function validateReceipt() {
        $response = $this->makeRequest();

        $decoded_response = $this->decodeResponse($response);

        if (!isset($decoded_response->status) || $decoded_response->status != 0) {
            throw new Exception('Invalid receipt. Status code: ' . (!empty($decoded_response->status) ? $decoded_response->status : 'N/A'));
        }

        if (!is_object($decoded_response)) {
            throw new Exception('Invalid response data');
        }

        return $decoded_response;
    }

    private function encodeRequest() {
        return json_encode(array('receipt-data' => $this->getReceipt()));
    }

    private function decodeResponse($response) {
        return json_decode($response);
    }

    private function makeRequest() {

		$data = $this->encodeRequest();
		
    	// use key 'http' even if you send the request to https://...
    	// This: 'content' => http_build_query($data),
    	// seems to generate an error (21002)
    	$options = array(
        	'http' => array(
            'header'  => "Content-type: application/json",
            'method'  => 'POST',
            'content' => $data
       		 ),
    	);
    	$context  = stream_context_create($options);
    	$response = file_get_contents($this->endpoint, false, $context);

        return $response;
    }
}
