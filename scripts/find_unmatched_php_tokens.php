<?php
$code = file_get_contents(__DIR__ . '/../routes/web.php');
$tokens = token_get_all($code);
$stack = [];
$line = 1;
foreach ($tokens as $tok) {
    if (is_array($tok)) {
        $tokenName = token_name($tok[0]);
        $line = $tok[2];
        if ($tok[0] === T_CURLY_OPEN || $tok[0] === T_DOLLAR_OPEN_CURLY_BRACES) {
            $stack[] = $line;
        }
        // ignore comments and strings
        if ($tok[0] === T_COMMENT || $tok[0] === T_DOC_COMMENT || $tok[0] === T_CONSTANT_ENCAPSED_STRING || $tok[0] === T_ENCAPSED_AND_WHITESPACE) {
            continue;
        }
        // count braces in token content if any (usually not)
        $text = $tok[1];
    } else {
        $text = $tok;
        // update line for non-array tokens by counting newlines
        $line += substr_count($text, "\n");
    }
    // for any token text (including non-array) inspect characters
    for ($i = 0; $i < strlen($text); $i++) {
        $ch = $text[$i];
        if ($ch === '{') {
            $stack[] = $line;
        } elseif ($ch === '}') {
            array_pop($stack);
        }
    }
}
if (count($stack) === 0) {
    echo "No unmatched opening braces\n";
} else {
    echo "Unmatched opening brace(s) at lines: " . implode(', ', $stack) . "\n";
}
?>