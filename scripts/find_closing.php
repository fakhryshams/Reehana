<?php
$lines = file(__DIR__ . '/../routes/web.php');
foreach ($lines as $i => $line) {
    if (strpos($line, '});') !== false) {
        echo ($i+1) . ": " . rtrim($line) . PHP_EOL;
    }
}
?>