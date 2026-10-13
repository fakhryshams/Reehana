<?php
$code = file_get_contents(__DIR__ . '/../routes/web.php');
$tokens = token_get_all($code);
$depth = 0;
foreach ($tokens as $tok) {
    if (is_array($tok)) {
        $text = $tok[1];
        $line = $tok[2];
        if ($tok[0] === T_COMMENT || $tok[0] === T_DOC_COMMENT || $tok[0] === T_CONSTANT_ENCAPSED_STRING || $tok[0] === T_ENCAPSED_AND_WHITESPACE) {
            // ignore these
        }
    } else {
        $text = $tok;
        // no line info
        $line = null;
    }
    for ($i = 0; $i < strlen($text); $i++) {
        $ch = $text[$i];
        if ($ch === '{') {
            $depth++;
            echo "line:" . ($line?:'?') . " depth:+$depth char at pos $i\n";
        } elseif ($ch === '}') {
            echo "line:" . ($line?:'?') . " depth:-$depth char at pos $i\n";
            $depth--;
        }
    }
}
if ($depth !== 0) echo "FINAL DEPTH: $depth\n";
?>