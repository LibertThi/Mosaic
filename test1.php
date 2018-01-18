<?php
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;
$img = "F:/img/1445.png";
$quality = 5;
$palette = ColorThief::getPalette($img, 5, $quality);
$dominantColor = ColorThief::getColor($img, $quality);

print_r($dominantColor);
print_r($palette);
?>