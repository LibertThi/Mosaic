<?php
require_once 'vendor/autoload.php';
require_once 'inc/dbWorker.php';
require_once 'inc/tileTask.php';

define("IMG_PATH","img"); // Define here where all the images (to create mosaic) are
use ColorThief\ColorThief;
$imagine = new Imagine\Gd\Imagine();

// Options:
$opt = getopt("",["rows:", "columns:", "img:", "tilesize:", "tileres:"]);
/*
--columns <int> : Number of columns for the mosaic. Overrides Rows if both are used
--rows <int> : Number of rows for the mosaic. Overridden by Columns if both are used
--img <path> : Path of the image to process. Must be set
--tilesize <int> : Set tilesize for the mosaic. Overridden by columns and rows if they are defined
--tileres <int> : Set the resolution (in pixels) of each tile. Beware, as it uses a lot of memory !
*/
$columns = 0;
$rows = 0;
$baseImgPath = "";
$tileSize = 0;
$tileRes = 0;

if (!$opt){
    echo "Usage: php [script] [options]\n";
    echo "--columns <int> : Number of columns for the mosaic. Overrides Rows if both are used
--rows <int> : Number of rows for the mosaic. Overridden by Columns if both are used
--img <path> : Path of the image to process. Must be set
--tilesize <int> : Set tilesize for the mosaic. Overridden by columns or rows if any is defined
--tileres <int> : Set the resolution (in pixels) of each tile. USE WITH CAUTION, as it may exceed maximum memory !\n";
    exit;
}
// Set "configuration" based on options
if (isset($opt['img']) and !empty($opt['img'])){
    $baseImgPath = $opt['img'];
    if (!is_file($baseImgPath)){
        echo "ERROR: File '$baseImgPath' not found";
        exit;
    }
}
else{
    echo "ERROR: You must specify an image path with '--img' option.";
    exit;
}

if (isset($opt['columns']) and intval($opt['columns']) != 0){
    $columns = (int) $opt['columns']; 
}
if (isset($opt['rows']) and intval($opt['rows']) != 0 and $columns == 0){
    $rows = (int) $opt['rows'];
}
if (isset($opt['tilesize']) and intval($opt['tilesize']) != 0 and $columns == 0 and $rows == 0){
    $tileSize = (int) $opt['tilesize'];
}
if (isset($opt['tileres']) and intval($opt['tileres']) != 0){
    $tileRes = $opt['tileres'];
    ini_set('memory_limit','-1'); // Change here memory limit. Can try with -1 but it's BAD
}
else if (!is_file($baseImgPath)){
    echo "ERROR: File '$baseImgPath' not found";
    exit;
}

if ($columns == 0 and $rows == 0 and $tileSize == 0){
    echo 'ERROR: You must define a number of columns / rows or tilesize';
    exit;
}

// Get start time
$startTime = microtime(true);

// Load img based on type
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
        $baseImg = imagecreatefrompng($baseImgPath);
        break;
}

// create base for mosaic img
$baseImgSize = getimagesize($baseImgPath);

// Set tileSize based on options if columns or rows is used
if ($tileSize == 0){
    if ($columns != 0){
        $tileSize = ceil($baseImgSize[0] / $columns);
    }
    elseif ($rows != 0){
        $tileSize = ceil($baseImgSize[1] / $rows);
    }
}

// Adapt scaling is using High res.
// Each tile will be larger, resulting in a bigger resolution mosaic
if ($tileRes != 0){
    $scaling = $tileRes / $tileSize;
}
else{
    $scaling = 1;
}

$columns = ceil($baseImgSize[0] / $tileSize);
$rows = ceil($baseImgSize[1] / $tileSize);
$mosaicWidth = $tileSize * $columns * $scaling;
$mosaicHeight = $tileSize * $rows * $scaling;
$pattern = "/(\w+?)\.(\w*)/";
$mosaicImgPath = preg_replace($pattern, "$1-" . time() . ".$2", $baseImgPath);
$mosaicSize = new Imagine\Image\Box($mosaicWidth,$mosaicHeight);
$mosaicImg = $imagine->create($mosaicSize);

$pool = new Pool(150, 'Connection', ["root","","mosaic"]);
$datas = [];

// vertical
for ($y = 0; $y < $mosaicHeight / $scaling ; $y += $tileSize){
    // horizontal
    for ($x = 0; $x < $mosaicWidth / $scaling ; $x += $tileSize){
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
echo "Creating mosaic...\n";
foreach ($datas as $data){
    $point = new Imagine\Image\Point($data->x * $scaling, $data->y * $scaling);
    $imageFromFile = $imagine->open(IMG_PATH . "/$data->imgId.$data->imgExt");
    $imageFromFile->resize(new Imagine\Image\Box($data->tileSize * $scaling, $data->tileSize* $scaling));
    $mosaicImg->paste($imageFromFile,$point);
    unset($imageFromFile);
}
// crop to fit base img and save
$mosaicImg = $mosaicImg->crop(new Imagine\Image\Point(0,0), new Imagine\Image\Box($baseImgSize[0] * $scaling, $baseImgSize[1] * $scaling));
$mosaicImg->save($mosaicImgPath);

// output total time spent
$endTime = microtime(true);
$timeSpent = date("H:i:s", $endTime - $startTime);
echo "Mosaic created on '$mosaicImgPath' !\n$timeSpent to create\n";
?>