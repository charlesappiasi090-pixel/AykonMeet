<?php

$code = file_get_contents('app/Http/Controllers/MultiCompanyMeetingController.php');
$tokens = token_get_all('<?php '.$code);
$errors = [];
foreach ($tokens as $i => $token) {
    if (is_array($token) && $token[0] === T_PRIVATE) {
        // Check next tokens
        $next = $tokens[$i + 1] ?? null;
        $next2 = $tokens[$i + 2] ?? null;
        if (is_array($next) && $next[0] === T_FUNCTION) {
            // Check if previous token was a closing brace
            $prev = $tokens[$i - 1] ?? null;
            if (! ($prev && (is_string($prev) && $prev === '}' || (is_array($prev) && $prev[0] === T_CURLY_CLOSE)))) {
                echo "Potential issue at token $i: private function without preceding closing brace\n";
            }
        }
    }
}
echo "Done checking\n";
