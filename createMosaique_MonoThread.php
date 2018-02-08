<?php
require_once 'vendor/autoload.php';
require_once 'connectDb.php';
use ColorThief\ColorThief;
$imagine = new Imagine\Imagick\Imagine();

require_once("dbWorker.php");
define("IMG_DIR","F:/img");
define("WORK_DIR","F:/");

$baseImgPath = WORK_DIR . "test1.jpg";
$tileSize = 20;

if (!is_file($baseImgPath)) echo 'ERROR: File not found';

$baseImg = new Imagick();
$baseImg->readImage($baseImgPath);
$baseWidth = $baseImg->getImageWidth();
$baseHeight = $baseImg->getImageHeight();

$nbTilesX = ceil($baseWidth / $tileSize);
$nbTilesY = ceil($baseHeight / $tileSize);

$mosaicWidth = $tileSize * $nbTilesX;
$mosaicHeight = $tileSize * $nbTilesY;
$mosaicImgPath = str_replace(".","-mosaic.",$baseImgPath);

$mosaicSize = new Imagine\Image\Box($mosaicWidth,$mosaicHeight);
$mosaicImg = $imagine->create($mosaicSize);

$pdo = getConnection('mosaique');
$queryString =
'SELECT tbl_images.numero, tbl_images.fileExtension, pow(tbl_colors.red - :red, 2)+pow(tbl_colors.green - :green,2)+pow(tbl_colors.blue - :blue,2) as ratio
    FROM tbl_images
    JOIN tbl_colorsinimages 
        ON tbl_images.numero = tbl_colorsinimages.num_tbl_images
    JOIN tbl_colors
        ON tbl_colors.numero = tbl_colorsinimages.num_tbl_colors 
    WHERE tbl_colorsinimages.priority = 1
    ORDER BY ratio 
    LIMIT 1';

$stmt = $pdo->prepare($queryString);

// vert
for ($y = 0; $y < $mosaicHeight; $y += $tileSize){
    // hor
    for ($x = 0; $x < $mosaicWidth; $x += $tileSize){
        // Get tile
        $baseTile = $baseImg->getImageRegion($tileSize,$tileSize,$x,$y);
        // Get tile color
        $tileColor = ColorThief::getColor($baseTile,10);

        // create a new tile with color
        /*$palette = new Imagine\Image\Palette\RGB();
        $color = $palette->color($tileColor);
        $newTile = $imagine->create(new Imagine\Image\Box($tileSize,$tileSize),$color);*/
  

        // replace tile with corresponding img
        $stmt->bindValue(':red',$tileColor[0],PDO::PARAM_INT);
        $stmt->bindValue(':green',$tileColor[1],PDO::PARAM_INT);
        $stmt->bindValue(':blue',$tileColor[2],PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $imgId = "{$row->numero}";
        $imgExt = "{$row->fileExtension}";

        $imageFromFile = $imagine->open(IMG_DIR . "/$imgId.$imgExt");
        $imageFromFile->resize(new Imagine\Image\Box($tileSize,$tileSize));

        // add tile to mosaic image
        $point = new Imagine\Image\Point($x,$y);
        $mosaicImg->paste($imageFromFile,$point);
    }
}

$mosaicImg->save($mosaicImgPath);

?>