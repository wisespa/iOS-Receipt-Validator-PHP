<?php

include __DIR__ . '/../itunesReceiptValidator.php';

    function format_json($json, $html = false, $tabspaces = null)
    {
        $tabcount = 0;
        $result = '';
        $inquote = false;
        $ignorenext = false;

        if ($html) {
            $tab = str_repeat("&nbsp;", ($tabspaces == null ? 4 : $tabspaces));
            $newline = "<br/>";
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

    print 'Environment: ';
    print (($rv->getEndpoint() === itunesReceiptValidator::SANDBOX_URL) ? 'Sandbox' : 'Production');
    print '<br />';

    $info = $rv->validateReceipt();
    echo 'Success! The returned JSON is: <br />';
    echo format_json($info, true);
}
catch (Exception $ex) {
    echo $ex->getMessage() . '<br />';
}
