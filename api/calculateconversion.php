<?php

$type = $_GET['c'] ?? 'b';
$input = intval($_GET['input']);

$rate = 15;

if ($input < 0) {
  $input = 0;
}

if ($type == 'b') {
  $output = $input * $rate;
  $text = 'Tix';
} else {
  $output = floor($input / $rate);
  $text = 'R$';
}

echo $output;

?>
