<?php
require_once 'vendor/autoload.php';
require_once 'connectDb.php';
use ColorThief\ColorThief;
require_once("dbWorker.php");
define("IMG_DIR","F:/img");
define("WORK_DIR","F:/");

$baseImgPath = WORK_DIR . "test.jpg";
$tileSize = 50;

if (!is_file($baseImgPath)) echo 'ERROR: File not found';

$baseImg = new Imagick();
$baseImg->readImage($baseImgPath);
$baseWidth = $baseImg->getImageWidth();
$baseHeight = $baseImg->getImageHeight();
$nbTilesX = ceil($baseWidth / $tileSize);
$nbTilesY = ceil($baseHeight / $tileSize);
$newWidth = $tileSize * $nbTilesX;
$newHeight = $tileSize * $nbTilesY;

$stack = new Imagick();

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
$stmt->bindParam(':red',$tileColor[0],PDO::PARAM_INT);
$stmt->bindParam(':green',$tileColor[1],PDO::PARAM_INT);
$stmt->bindParam(':blue',$tileColor[2],PDO::PARAM_INT);

// vert
for ($y = 0; $y < $newHeight; $y += $tileSize){
    // hor
    for ($x = 0; $x < $newWidth; $x += $tileSize){
        // Get tile
        $baseTile = $baseImg->getImageRegion($tileSize,$tileSize,$x,$y);
        $tileColor = ColorThief::getColor($baseTile,10); 
        print_r($tileColor);
        $newTile = new Imagick();

        // replace tile with color only
        //$newTile->newImage($tileSize,$tileSize,"rgb($tileColor[0],$tileColor[1],$tileColor[2])");

        // replace tile with corresponding img
        $stmt->execute();
        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $imgId = "{$row->numero}";
        $imgExt = "{$row->fileExtension}";
        $newTile->readImage(IMG_DIR . "/$imgId.$imgExt");
        $newTile->resizeimage($tileSize, $tileSize, \Imagick::FILTER_LANCZOS, 1.0, true);

        $stack->addImage($newTile);
    }
}


$mosaic = $stack->montageImage(new ImagickDraw(), $nbTilesX . 'x' . $nbTilesY, $tileSize . 'x' . $tileSize, 0, '0');
$mosaic->cropImage($baseWidth, $baseHeight, 0, 0);

// Save img

$mosaic->setImageFormat("png");
$mosaic->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
$mosaic->setImageCompressionQuality(0);
$mosaicImgPath = str_replace(".","-mosaic.",$baseImgPath);
$mosaic->writeImage($mosaicImgPath);
?>