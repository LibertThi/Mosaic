<?php
require_once 'vendor/autoload.php';
require_once 'dbWorker.php';
require_once 'tileTask.php';
use ColorThief\ColorThief;
$imagine = new Imagine\Gd\Imagine();

require_once("dbWorker.php");
define("IMG_DIR","F:/img");
define("WORK_DIR","F:/");

$baseImgPath = WORK_DIR . "test1.jpg";
$tileSize = 20;

if (!is_file($baseImgPath)) echo 'ERROR: File not found';

$baseImg = imagecreatefromjpeg($baseImgPath);
$baseImgSize = getimagesize($baseImgPath);

$nbTilesX = ceil($baseImgSize[0] / $tileSize);
$nbTilesY = ceil($baseImgSize[1] / $tileSize);

$mosaicWidth = $tileSize * $nbTilesX;
$mosaicHeight = $tileSize * $nbTilesY;
$mosaicImgPath = str_replace(".","-mosaic.",$baseImgPath);

$mosaicSize = new Imagine\Image\Box($mosaicWidth,$mosaicHeight);
$mosaicImg = $imagine->create($mosaicSize);

$pool = new Pool(16, 'Connection', ["root","","mosaique"]);

// vert
for ($y = 0; $y < $mosaicHeight; $y += $tileSize){
    // hor
    for ($x = 0; $x < $mosaicWidth; $x += $tileSize){
        $task = new TileTask($baseImg,$x,$y,$tileSize);
        $pool->submit($task);
        while($pool->collect());    
    }
}
$pool->shutdown();
$mosaicImg->save($mosaicImgPath);

?>