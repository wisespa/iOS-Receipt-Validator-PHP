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

        return array(
            'quantity'       =>  $decoded_response->receipt->quantity,
            'product_id'     =>  $decoded_response->receipt->product_id,
            'transaction_id' =>  $decoded_response->receipt->transaction_id,
            'purchase_date'  =>  $decoded_response->receipt->purchase_date,
            'app_item_id'    =>  $decoded_response->receipt->app_item_id,
            'bid'            =>  $decoded_response->receipt->bid,
            'bvrs'           =>  $decoded_response->receipt->bvrs
        );
    }

    private function encodeRequest() {
        return json_encode(array('receipt-data' => $this->getReceipt()));
    }

    private function decodeResponse($response) {
        return json_decode($response);
    }

    private function makeRequest() {
//         $ch = curl_init($this->endpoint);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
//         curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
//         curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
//         curl_setopt($ch, CURLOPT_POST, true);
//         curl_setopt($ch, CURLOPT_POSTFIELDS, $this->encodeRequest());
// 
//         $response = curl_exec($ch);
//         $errno    = curl_errno($ch);
//         $errmsg   = curl_error($ch);
//         curl_close($ch);
// 
//         if ($errno != 0) {
//             throw new Exception($errmsg, $errno);
//         }

$data = '{"receipt-data" : "'.$this->getReceipt().'"}';

$data = '{"receipt-data" : "MIITtQYJKoZIhvcNAQcCoIITpjCCE6ICAQExCzAJBgUrDgMCGgUAMIIDZgYJKoZIhvcNAQcBoIIDVwSCA1MxggNPMAoCAQgCAQEEAhYAMAoCARICAQEEAhYAMAoCARMCAQEEAgwAMAoCARQCAQEEAgwAMAsCAQECAQEEAwIBADALAgELAgEBBAMCAQAwCwIBDgIBAQQDAgFSMAsCAQ8CAQEEAwIBADALAgEQAgEBBAMCAQAwCwIBGQIBAQQDAgEDMAwCAQoCAQEEBBYCNCswDQIBDQIBAQQFAgMBEXQwDgIBCQIBAQQGAgRQMjI4MA8CAQMCAQEEBwwFMS4wLjAwGAIBBAIBAgQQkcU6EZ/NXh6anSkBeRNhGjAbAgEAAgEBBBMMEVByb2R1Y3Rpb25TYW5kYm94MBwCAQUCAQEEFJACiAm+09EfKJqq/THaH50E3S6AMB4CAQICAQEEFgwUbmV0LmNsb3VkYXJrLmlnbGlrZXMwHgIBDAIBAQQWFhQyMDE0LTAxLTI3VDE2OjMyOjI5WjBCAgEHAgEBBDrThMYiYwNNoDhAHDebAqq9QR17MNXZmR+sttNue7LtGLm0xQhYMJj1eVz5LtwaLdmT9Wlf/KJ2ercoMFUCAQYCAQEETZxXtwXWeNVeCWHt6zlpiPSTlylvWMT+J1RMyP8P3iQJB3VrUZDq2NnUrVKvqAsL8QvadpWG8z+RYRcOaslJvtiJrXMDCshAVuowJA9zMIIBXwIBEQIBAQSCAVUxggFRMAsCAgasAgEBBAIWADALAgIGrQIBAQQCDAAwCwICBrACAQEEAhYAMAsCAgayAgEBBAIMADALAgIGswIBAQQCDAAwCwICBrQCAQEEAgwAMAsCAga1AgEBBAIMADALAgIGtgIBAQQCDAAwDAICBqUCAQEEAwIBATAMAgIGqwIBAQQDAgEBMAwCAgauAgEBBAMCAQAwDAICBq8CAQEEAwIBADAMAgIGsQIBAQQDAgEAMBsCAganAgEBBBIMEDEwMDAwMDAwOTk3MzE5NzgwGwICBqkCAQEEEgwQMTAwMDAwMDA5OTczMTk3ODAfAgIGqAIBAQQWFhQyMDE0LTAxLTI3VDE2OjMyOjI5WjAfAgIGqgIBAQQWFhQyMDE0LTAxLTI3VDE2OjEyOjUyWjAlAgIGpgIBAQQcDBpuZXQuaWdwcm8uaWdsaWtlcy4xMDBjb2luc6CCDlUwggVrMIIEU6ADAgECAggYWUMhcnSc/DANBgkqhkiG9w0BAQUFADCBljELMAkGA1UEBhMCVVMxEzARBgNVBAoMCkFwcGxlIEluYy4xLDAqBgNVBAsMI0FwcGxlIFdvcmxkd2lkZSBEZXZlbG9wZXIgUmVsYXRpb25zMUQwQgYDVQQDDDtBcHBsZSBXb3JsZHdpZGUgRGV2ZWxvcGVyIFJlbGF0aW9ucyBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTAeFw0xMDExMTEyMTU4MDFaFw0xNTExMTEyMTU4MDFaMHgxJjAkBgNVBAMMHU1hYyBBcHAgU3RvcmUgUmVjZWlwdCBTaWduaW5nMSwwKgYDVQQLDCNBcHBsZSBXb3JsZHdpZGUgRGV2ZWxvcGVyIFJlbGF0aW9uczETMBEGA1UECgwKQXBwbGUgSW5jLjELMAkGA1UEBhMCVVMwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQC2k8K3DyRe7dI0SOiFBeMzlGZb6Cc3v3tDSev5yReXM3MySUrIb2gpFLiUpvRlSztH19EsZku4mNm89RJRy+YvqfSznxzoKPxSwIGiy1ZigFqika5OQMN9KC7X0+1N2a2K+/JnSOzreb0CbQRZGP+MN5+KN/Fi/7uiA1CHCtWS4IYRXiNG9eElYyuiaoyyELeRI02aP4NA8mQJWveNrlZc1PW0bgMbBF0sG68AmRfXpftJkc7ioRExXhkBwNrOUINeyOtJO0kaKurgn7/SRkmc2Kuhg2FsD8H8s62ZdSr8I5vvIgjre1kUEZ9zNC3muTmmO/fmPuzKpvurrybfj4iBAgMBAAGjggHYMIIB1DAMBgNVHRMBAf8EAjAAMB8GA1UdIwQYMBaAFIgnFwmpthhgi+zruvZHWcVSVKO3ME0GA1UdHwRGMEQwQqBAoD6GPGh0dHA6Ly9kZXZlbG9wZXIuYXBwbGUuY29tL2NlcnRpZmljYXRpb25hdXRob3JpdHkvd3dkcmNhLmNybDAOBgNVHQ8BAf8EBAMCB4AwHQYDVR0OBBYEFHV2JKJrYgyXNKH6Tl4IDCK/c+++MIIBEQYDVR0gBIIBCDCCAQQwggEABgoqhkiG92NkBQYBMIHxMIHDBggrBgEFBQcCAjCBtgyBs1JlbGlhbmNlIG9uIHRoaXMgY2VydGlmaWNhdGUgYnkgYW55IHBhcnR5IGFzc3VtZXMgYWNjZXB0YW5jZSBvZiB0aGUgdGhlbiBhcHBsaWNhYmxlIHN0YW5kYXJkIHRlcm1zIGFuZCBjb25kaXRpb25zIG9mIHVzZSwgY2VydGlmaWNhdGUgcG9saWN5IGFuZCBjZXJ0aWZpY2F0aW9uIHByYWN0aWNlIHN0YXRlbWVudHMuMCkGCCsGAQUFBwIBFh1odHRwOi8vd3d3LmFwcGxlLmNvbS9hcHBsZWNhLzAQBgoqhkiG92NkBgsBBAIFADANBgkqhkiG9w0BAQUFAAOCAQEAoDvxh7xptLeDfBn0n8QCZN8CyY4xc8scPtwmB4v9nvPtvkPWjWEt5PDcFnMB1jSjaRl3FL+5WMdSyYYAf2xsgJepmYXoePOaEqd+ODhk8wTLX/L2QfsHJcsCIXHzRD/Q4nth90Ljq793bN0sUJyAhMWlb1hZekYxQWi7EzVFQqSM+hHVSxbyMjXeH7zSmV3I5gIyWZDojcs53yHaw3b7ejYaFhqYTIUb5itFLS9ZGi3GmtZmkqPSNlJQgCBNM8iymtZTYrFgUvD1930QUOQSv71xvrSAx23Eb1s5NdHnt96BICeOOFyChzpzYMTW8RygqWZEfs4MKJsjf6zs5qA73TCCBCMwggMLoAMCAQICARkwDQYJKoZIhvcNAQEFBQAwYjELMAkGA1UEBhMCVVMxEzARBgNVBAoTCkFwcGxlIEluYy4xJjAkBgNVBAsTHUFwcGxlIENlcnRpZmljYXRpb24gQXV0aG9yaXR5MRYwFAYDVQQDEw1BcHBsZSBSb290IENBMB4XDTA4MDIxNDE4NTYzNVoXDTE2MDIxNDE4NTYzNVowgZYxCzAJBgNVBAYTAlVTMRMwEQYDVQQKDApBcHBsZSBJbmMuMSwwKgYDVQQLDCNBcHBsZSBXb3JsZHdpZGUgRGV2ZWxvcGVyIFJlbGF0aW9uczFEMEIGA1UEAww7QXBwbGUgV29ybGR3aWRlIERldmVsb3BlciBSZWxhdGlvbnMgQ2VydGlmaWNhdGlvbiBBdXRob3JpdHkwggEiMA0GCSqGSIb3DQEBAQUAA4IBDwAwggEKAoIBAQDKOFSmy1aqyCQ5SOmM7uxfuH8mkbw0U3rOfGOAYXdkXqUHI7Y5/lAtFVZYcC1+xG7BSoU+L/DehBqhV8mvexj/avoVEkkVCBmsqtsqMu2WY2hSFT2Miuy/axiV4AOsAX2XBWfODoWVN2rtCbauZ81RZJ/GXNG8V25nNYB2NqSHgW44j9grFU57Jdhav06DwY3Sk9UacbVgnJ0zTlX5ElgMhrgWDcHld0WNUEi6Ky3klIXh6MSdxmilsKP8Z35wugJZS3dCkTm59c3hTO/AO0iMpuUhXf1qarunFjVg0uat80YpyejDi+l5wGphZxWy8P3laLxiX27Pmd3vG2P+kmWrAgMBAAGjga4wgaswDgYDVR0PAQH/BAQDAgGGMA8GA1UdEwEB/wQFMAMBAf8wHQYDVR0OBBYEFIgnFwmpthhgi+zruvZHWcVSVKO3MB8GA1UdIwQYMBaAFCvQaUeUdgn+9GuNLkCm90dNfwheMDYGA1UdHwQvMC0wK6ApoCeGJWh0dHA6Ly93d3cuYXBwbGUuY29tL2FwcGxlY2Evcm9vdC5jcmwwEAYKKoZIhvdjZAYCAQQCBQAwDQYJKoZIhvcNAQEFBQADggEBANoyAJbFVJTTO4I3Zn0uaNXDxrjLJoxIkM8TJGpGjmPU8NATBt3YxME3FfIzEzkmLc4uVUDjCwOv+hLC5w0huNWAz6woL84ts06vhhkExulQ3UwpRxAj/Gy7G5hrSInhW53eRts1hTXvPtDiWEs49O11Wh9ccB1WORLl4Q0R5IklBr3VtBWOXtBZl5DpS4Hi3xivRHQeGaA6R8yRHTrrI1r+pS2X93u71odGQoXrUj0msmOotLHKj/TM4rPIR+C/mlmD+tqYUyqC9XxlLpXZM1317WXMMTfFWgToa+HniANKdZ6bKMtKQIhlQ3XdyzolI8WeV/guztKpkl5zLi8ldRUwggS7MIIDo6ADAgECAgECMA0GCSqGSIb3DQEBBQUAMGIxCzAJBgNVBAYTAlVTMRMwEQYDVQQKEwpBcHBsZSBJbmMuMSYwJAYDVQQLEx1BcHBsZSBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTEWMBQGA1UEAxMNQXBwbGUgUm9vdCBDQTAeFw0wNjA0MjUyMTQwMzZaFw0zNTAyMDkyMTQwMzZaMGIxCzAJBgNVBAYTAlVTMRMwEQYDVQQKEwpBcHBsZSBJbmMuMSYwJAYDVQQLEx1BcHBsZSBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eTEWMBQGA1UEAxMNQXBwbGUgUm9vdCBDQTCCASIwDQYJKoZIhvcNAQEBBQADggEPADCCAQoCggEBAOSRqQkfkdseR1DrBe1eeYQt6zaiV0xV7IsZid75S2z1B6siMALoGD74UAnTf0GomPnRymacJGsR0KO75Bsqwx+VnnoMpEeLW9QWNzPLxA9NzhRp0ckZcvVdDtV/X5vyJQO6VY9NXQ3xZDUjFUsVWR2zlPf2nJ7PULrBWFBnjwi0IPfLrCwgb3C2PwEwjLdDzw+dPfMrSSgayP7OtbkO2V4c1ss9tTqt9A8OAJILsSEWLnTVPA3bYharo3GSR1NVwa8vQbP4++NwzeajTEV+H0xrUJZBicR0YgsQg0GHM4qBsTBY7FoEMoxos48d3mVz/2deZbxJ2HafMxRloXeUyS0CAwEAAaOCAXowggF2MA4GA1UdDwEB/wQEAwIBBjAPBgNVHRMBAf8EBTADAQH/MB0GA1UdDgQWBBQr0GlHlHYJ/vRrjS5ApvdHTX8IXjAfBgNVHSMEGDAWgBQr0GlHlHYJ/vRrjS5ApvdHTX8IXjCCAREGA1UdIASCAQgwggEEMIIBAAYJKoZIhvdjZAUBMIHyMCoGCCsGAQUFBwIBFh5odHRwczovL3d3dy5hcHBsZS5jb20vYXBwbGVjYS8wgcMGCCsGAQUFBwICMIG2GoGzUmVsaWFuY2Ugb24gdGhpcyBjZXJ0aWZpY2F0ZSBieSBhbnkgcGFydHkgYXNzdW1lcyBhY2NlcHRhbmNlIG9mIHRoZSB0aGVuIGFwcGxpY2FibGUgc3RhbmRhcmQgdGVybXMgYW5kIGNvbmRpdGlvbnMgb2YgdXNlLCBjZXJ0aWZpY2F0ZSBwb2xpY3kgYW5kIGNlcnRpZmljYXRpb24gcHJhY3RpY2Ugc3RhdGVtZW50cy4wDQYJKoZIhvcNAQEFBQADggEBAFw2mUwteLftjJvc83eb8nbSdzBPwR+Fg4UbmT1HN/Kpm0COLNSxkBLYvvRzm+7SZA/LeU802KI++Xj/a8gH7H05g4tTINM4xLG/mk8Ka/8r/FmnBQl8F0BWER5007eLIztHo9VvJOLr0bdw3w9F4SfK8W147ee1Fxeo3H4iNcol1dkP1mvUoiQjEfehrI9zgWDGG1sJL5Ky+ERI8GA4nhX1PSZnIIozavcNgs/e66Mv+VNqW2TAYzN39zoHLFbr2g8hDtq6cxlPtdk2f8GHVdmnmbkyQvvY1XGefqFStxu9k0IkEirHDx22TZxeY8hLgBdQqorV2uT80AkHN7B1dSExggHLMIIBxwIBATCBozCBljELMAkGA1UEBhMCVVMxEzARBgNVBAoMCkFwcGxlIEluYy4xLDAqBgNVBAsMI0FwcGxlIFdvcmxkd2lkZSBEZXZlbG9wZXIgUmVsYXRpb25zMUQwQgYDVQQDDDtBcHBsZSBXb3JsZHdpZGUgRGV2ZWxvcGVyIFJlbGF0aW9ucyBDZXJ0aWZpY2F0aW9uIEF1dGhvcml0eQIIGFlDIXJ0nPwwCQYFKw4DAhoFADANBgkqhkiG9w0BAQEFAASCAQBn72I42Jk42YOzIKX/8Oo7WaFMWRtqeepT87nUTYfcC+v+iFDTMrZL0HLFLFFgZ9AGv5Z8ONnuunJ3hfTmVfA/ArkRdRFGndtjWn3osjprVvIvR/xNYcKObeoOKkyxOqEA8GhEODTz6s+OfsLoQ8WVlJQsaAVZAgDUAi3M7UqDlfiTwT1+Ua8q1xFD87fLyhgb+c9momDpG3lIFArW3QUrneGet03yzAywovxcrccRYssIF44CzFiCrT1ut3lBaI4b2elNaw4TDPZil0PUPzKL6jrKMMoFIklsvj6jiYXmd4uqG0oyqsoUOufLYUkSk1E0XJqYgKG2QxqTDQEIf3PP"}';

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
