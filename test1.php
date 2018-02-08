<?php
require_once 'vendor/autoload.php';
require_once 'connectDb.php';
use ColorThief\ColorThief;
require_once("dbWorker.php");
define("IMG_DIR","F:/img");
define("WORK_DIR","F:/");
require_once 'colors.inc.php';

$baseImgPath = WORK_DIR . "test3.jpg";

$color = ColorThief::getColor($baseImgPath,1);
echo 'colorthief';
print_r($color);
echo 'lib';
$lib = new GetMostCommonColors();

$i = imagecreatefromjpeg($baseImgPath);
$iX = imagesx($i);
$iY = imagesy($i);
$rTotal = 0;
$gTotal =0;
$bTotal = 0;
$total = 0;
for ($x = 0; $x < $iX; $x++) {
    for ($y = 0; $y < $iY; $y++) {
        $rgb = imagecolorat($i,$x,$y);
        $r   = ($rgb >> 16) & 0xFF;
        $g   = ($rgb >> 8) & 0xFF;
        $b   = $rgb & 0xFF;

        $rTotal += $r;
        $gTotal += $g;
        $bTotal += $b;
        $total++;
    }
}
$rAverage = round($rTotal/$total);
$gAverage = round($gTotal/$total);
$bAverage = round($bTotal/$total);
echo "$rAverage, $gAverage, $bAverage";
?>