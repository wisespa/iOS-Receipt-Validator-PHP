<?php

include __DIR__ . '/iapvalidator.php';

    function format_json($json, $html = false, $tabspaces = null)
    {
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;

        if ($html) {
            $tab = str_repeat("&nbsp;", ($tabspaces == null ? 4 : $tabspaces));
            $newline = "\n";
        } else {
            $tab = ($tabspaces == null ? "\t" : str_repeat(" ", $tabspaces));
            $newline = "\n";
        }

        for($i = 0; $i < strlen($json); $i++) {
            $char = $json[$i];

            if ($ignorenext) {
                $result .= $char;
                $ignorenext = false;
            } else {
                switch($char) {
                    case ':':
                        $result .= $char . (!$inquote ? " " : "");
                        break;
                    case '{':
                        if (!$inquote) {
                            $tabcount++;
                            $result .= $char . $newline . str_repeat($tab, $tabcount);
                        }
                        else {
                            $result .= $char;
                        }
                        break;
                    case '}':
                        if (!$inquote) {
                            $tabcount--;
                            $result = trim($result) . $newline . str_repeat($tab, $tabcount) . $char;
                        }
                        else {
                            $result .= $char;
                        }
                        break;
                    case ',':
                        if (!$inquote) {
                            $result .= $char . $newline . str_repeat($tab, $tabcount);
                        }
                        else {
                            $result .= $char;
                        }
                        break;
                    case '"':
                        $inquote = !$inquote;
                        $result .= $char;
                        break;
                    case '\\':
                        if ($inquote) $ignorenext = true;
                        $result .= $char;
                        break;
                    default:
                        $result .= $char;
                }
            }
        }

        return $result;
    }
echo "<pre>";

$receipt = NULL;
if (isset($_POST['receipt'])) {
    $receipt  = $_POST['receipt'];
    echo "Receipt:\n";
    echo $receipt;
}
else {
    print "No receipt to validate. Exiting.\n";
    return;
}

$product = NULL;
if (isset($_POST['product']) && trim($_POST['product']) !='') {
    $product  = $_POST['product'];
	echo "\nProduct: ".$product;
}

$secret = NULL;
if (isset($_POST['secret']) && trim($_POST['secret']) !='') {
    $secret  = $_POST['secret'];
    echo "\nSecret: ".$secret."\n";
}

try {
    $rv = new IAPValidator(isset($_GET['sandbox']), $receipt, $product, $secret);

    print 'Environment: ';
    print ( isset($_GET['sandbox'])  ? 'Sandbox' : 'Production');
    print "\n";
    print "\n";

    $json = $rv->validateReceipt(true);
    echo "Success! The returned JSON is: \n\n";
    
    echo json_encode($json, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES);
}
catch (Exception $ex) {
    echo $ex->getMessage() . "\n";
}

echo "</pre>";

