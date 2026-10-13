<?php
$lines = file(__DIR__ . '/../routes/web.php');
$stack = [];
foreach ($lines as $i => $line) {
    $lineNum = $i + 1;
    // iterate characters to handle multiple braces on a line
    $chars = str_split($line);
    foreach ($chars as $ch) {
        if ($ch === '{') {
            $stack[] = $lineNum;
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