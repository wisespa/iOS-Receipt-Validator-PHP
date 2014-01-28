<?php

include __DIR__ . '/../itunesReceiptValidator.php';

if (isset($_POST['receipt'])) {
    $receipt  = $_POST['receipt'];
}
else {
    print 'No receipt to validate. Exiting.<br />';
    return;
}

$endpoint = isset($_GET['sandbox']) ? itunesReceiptValidator::SANDBOX_URL : itunesReceiptValidator::PRODUCTION_URL;

try {
    $rv = new itunesReceiptValidator($endpoint, $receipt);

    print 'Environment: <br />';
    print (($rv->getEndpoint() === itunesReceiptValidator::SANDBOX_URL) ? 'Sandbox' : 'Production');

    $info = $rv->validateReceipt();
    echo 'Success';
    var_dump($info);
}
catch (Exception $ex) {
    echo $ex->getMessage() . '<br />';
}
