<?php
require_once 'vendor/autoload.php';
require_once 'dbWorker.php';
require_once 'tileTask.php';
use ColorThief\ColorThief;
$imagine = new Imagine\Gd\Imagine();

require_once("dbWorker.php");
define("IMG_DIR","F:/img");
define("WORK_DIR","F:/");

$baseImgPath = WORK_DIR . "nature.jpeg";
$tileSize = 50;

if (!is_file($baseImgPath)) echo 'ERROR: File not found';

$type = exif_imagetype($baseImgPath);
switch ($type){
    case IMAGETYPE_GIF:
        $baseImg = imagecreatefromgif($baseImgPath);
        break;
    case IMAGETYPE_JPEG:
        $baseImg = imagecreatefromjpeg($baseImgPath);
        break;                
    case IMAGETYPE_BMP:
        $baseImg = imagecreatefrombmp($baseImgPath);
        break;
    case IMAGETYPE_PNG:
    default:
        $baseImgimg = imagecreatefrompng($baseImgPath);
        break;
}
$baseImgSize = getimagesize($baseImgPath);

$nbTilesX = ceil($baseImgSize[0] / $tileSize);
$nbTilesY = ceil($baseImgSize[1] / $tileSize);

$mosaicWidth = $tileSize * $nbTilesX;
$mosaicHeight = $tileSize * $nbTilesY;
$mosaicImgPath = str_replace(".","-". time() . ".",$baseImgPath);

$mosaicSize = new Imagine\Image\Box($mosaicWidth,$mosaicHeight);
$mosaicImg = $imagine->create($mosaicSize);

$pool = new Pool(100, 'Connection', ["root","","mosaique"]);
$datas = [];

// vert
for ($y = 0; $y < $mosaicHeight; $y += $tileSize){
    // hor
    for ($x = 0; $x < $mosaicWidth; $x += $tileSize){
        // datas to pass to each thread
        $data = new Threaded();
        $data->x = $x;
        $data->y = $y;
        $data->baseImg = $baseImg;
        $data->tileSize = $tileSize;
        $datas[] = $data;

        $task = new TileTask($data);
        $pool->submit($task);
        while($pool->collect());    
    }
}
$pool->shutdown();

// create mosaic from all datas
echo "Creating mosaic...";
foreach ($datas as $data){
    $point = new Imagine\Image\Point($data->x,$data->y);
    $imageFromFile = $imagine->open(IMG_DIR . "/$data->imgId.$data->imgExt");
    $imageFromFile->resize(new Imagine\Image\Box($data->tileSize,$data->tileSize));
    $mosaicImg->paste($imageFromFile,$point);
}

$mosaicImg->save($mosaicImgPath);

?>