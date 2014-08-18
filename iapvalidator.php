<?php
class IAPValidator {

    const SANDBOX_URL    = 'https://sandbox.itunes.apple.com/verifyReceipt';
    const PRODUCTION_URL = 'https://buy.itunes.apple.com/verifyReceipt';

    protected $receipt;
    protected $product;
    protected $endpoint;
    protected $sharedSecret;
    
    function __construct($sandbox, $receipt, $product, $sharedSecret = NULL) {
        $this->receipt = $receipt;
        $this->product = $product;
        $this->sharedSecret = $sharedSecret;
        
        if ($sandbox) {
        		$this->endpoint = IAPValidator::SANDBOX_URL;
        } else {
        		$this->endpoint = IAPValidator::PRODUCTION_URL;
        }
    }

    private function encodeRequest() {
    		$receiptData = $this->receipt;
    		if (strpos($receiptData, '{') !== false) {
    			$receiptData = base64_encode($receiptData);
    		}  
    		
    		$requestData = array('receipt-data' => $receiptData);
    		if ($this->sharedSecret) {
    			$requestData['password'] = $this->sharedSecret;
    		}
        return json_encode($requestData);
    }

    private function decodeResponse($response) {
        return json_decode(trim($response));
    }

    // Assemble a json request
    // use key 'http' even if you send the request to https://...
    // This: 'content' => http_build_query($data),
    // seems to generate an error (21002)
    private function assemble_json_request($request, $dump_request = false){
	    	$context = stream_context_create(array('http' => array(
	    			'method' => "POST",
	    			'header' => "Content-Type: application/json",
	    			'content' => $request
	    	)));
	    	 
	    	if($dump_request){
	    		echo '<pre>';
	    		echo 'JSON Request is:<p>';
	    	  
	    		print_r($request);
	    		echo '</pre>';
	    	}
	    	return $context;
    }
    
    public function validateReceipt() {
	    	$resultMsg = array('isVerified' => true, 'isValid' => false, 'errorMsg' => '');

	    	if(strlen($this->receipt) < 1000) {
	    		$resultMsg['errorMsg'] = 'FAKE ORDER -- Receipt too short (< 1000)';
	    		$resultMsg['isValid'] = false;
	    		$resultMsg['isVerified'] = true;
	    		$resultMsg['receipt'] = $this->receipt;
	    		$resultMsg['receiptlen'] = strlen($this->receipt);

	    		return $resultMsg;
	    	}
    	 	    	
		$context = $this->assemble_json_request($this->encodeRequest());
	    	$file = file_get_contents($this->endpoint, false, $context);
	    	$response = $this->decodeResponse($file);
	    	
	    	if (!isset($response->status)) {
	    		$resultMsg['errorMsg'] = "FAILED -- Cannot connect to Apple receipt verification service at: $this->endpoint";
	    		$resultMsg['isValid'] = false;
	    		$resultMsg['isVerified'] = false;
	    		return $resultMsg;
	    	}
	    	
	    	if($response->status == 21007 && $this->endpoint === IAPValidator::PRODUCTION_URL) {
	    		// Sandbox receipt is sent to production IAP validation url
	    		// Usually happen when Apple review IAP product with release binary
	    		// So verify sanbox IAP instead
	    		$sanboxValidator = new IAPValidator(true, $this->receipt, $this->product);
	    		return $sanboxValidator->validateReceipt();
	    	}
	    	
	    	$ios7Format = isset($response->receipt->in_app) ? true : false;
	    	if($response->status == 0) {
	    		if(!$ios7Format){
	    			// iap format previous ios7
	    			if (isset($response->receipt->product_id) && $response->receipt->product_id == $this->product) {
	    				$resultMsg['isValid'] = true;
	    				$resultMsg['isVerified'] = true;
	    				$resultMsg['transaction_id'] = $response->receipt->original_transaction_id;
	    			} else {
	    				$resultMsg['errorMsg'] = "FAKE ORDER -- Product not match: ".$this->product. " Apple feedback: ".$response->receipt->product_id;
	    				$resultMsg['isValid'] = false;
	    				$resultMsg['isVerified'] = true;
	    			}
	    		} else{
	    			//ios 7 format
	    			$inapps = $response->receipt->in_app;
	    			$productFound = false;
	    			$fakeProducts = array();
	    			foreach ($inapps as $inapp) {
	    				if ( $inapp->product_id == $this->product ) {
	    					$productFound = true;
	    					$resultMsg['isValid'] = true;
	    					$resultMsg['isVerified'] = true;
	    					$resultMsg['transaction_id'] = $inapp->original_transaction_id;
	    					break;
	    				} else {
	    					$fakeProducts[] = $inapp->product_id;
	    				}
	    			}
	    			
	    			if (!$productFound) {
	    				$resultMsg['errorMsg'] = "FAKE ORDER -- Product not found: ".$this->product. " Apple feedback: ".implode(",", $fakeProducts);
	    				$resultMsg['isValid'] = false;
	    				$resultMsg['isVerified'] = true;
	    			}
	    		}
	    	} else {
	    			$resultMsg['errorMsg'] = "FAKE ORDER -- Invalid receipt, status code:$response->status";
	    			$resultMsg['isValid'] = false;
	    			$resultMsg['isVerified'] = true;
	    	}
	    	
        return $resultMsg;
    }
}
