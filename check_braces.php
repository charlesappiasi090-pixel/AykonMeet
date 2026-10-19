<?php

require 'vendor/autoload.php';
$code = file_get_contents('app/Http/Controllers/MultiCompanyMeetingController.php');
$tokens = token_get_all('<?php '.$code);
$braces = 0;
$line = 1;
foreach ($tokens as $i => $token) {
    if (is_array($token)) {
        if ($token[0] === T_CURLY_OPEN) {
            $braces++;
            echo "Line $line: OPEN brace (count: $braces) [token: T_CURLY_OPEN]\n";
        }
        if (isset($token[1])) {
            $line += substr_count($token[1], "\n");
        }
    } elseif (is_string($token)) {
        if ($token === '{') {
            $braces++;
            echo "Line $line: OPEN brace (string) (count: $braces)\n";
        } elseif ($token === '}') {
            $braces--;
            echo "Line $line: CLOSE brace (count: $braces)\n";
        }
        $line += substr_count($token, "\n");
    }
    if ($braces < 0) {
        echo "Negative brace count at token $i\n";
        break;
    }
}
echo "Final brace count: $braces\n";
