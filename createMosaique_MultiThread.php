<?php
require_once 'vendor/autoload.php';
require_once 'dbWorker.php';
require_once 'tileTask.php';
use ColorThief\ColorThief;
$imagine = new Imagine\Gd\Imagine();

require_once("dbWorker.php");
define("IMG_DIR","F:/img");
define("WORK_DIR","F:/");

$baseImgPath = WORK_DIR . "helya.jpg";
$tileSize = 5;

if (!is_file($baseImgPath)){
    echo 'ERROR: File not found';
    exit;
}

// get time
$startTime = microtime(true);

// load img based on type
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

// create base for mosaic img
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

// vertical zob
for ($y = 0; $y < $mosaicHeight; $y += $tileSize){
    // horizontal zob
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
// crop to fit base img
$mosaicImg = $mosaicImg->crop(new Imagine\Image\Point(0,0), new Imagine\Image\Box($baseImgSize[0],$baseImgSize[1]));
$mosaicImg->save($mosaicImgPath);

$endTime = microtime(true);
$timeSpent = date("H:i:s", $endTime - $startTime) . " to complete.";
echo $timeSpent;
?>