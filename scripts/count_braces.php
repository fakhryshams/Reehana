<?php
$s = file_get_contents(__DIR__ . '/../routes/web.php');
echo 'opens:' . substr_count($s, '{') . PHP_EOL;
echo 'closes:' . substr_count($s, '}') . PHP_EOL;
$diff = substr_count($s, '{') - substr_count($s, '}');
echo 'diff:' . $diff . PHP_EOL;
?>