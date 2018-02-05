<?php
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;
require_once("dbWorker.php");
define("IMG_DIR","F:/img");
define("WORK_DIR","F:/");

class CreateTileTask extends Threaded{

    public function __construct(Imagick $tile){
        $this->tile = $tile;
    }
    public function run(){
        include 'vendor/autoload.php';
        // Get tile color
        echo "Running\n";

        $tileColor = array('255','0','0');
        //$tileColor = ColorThief::getColor($this->tile,50);   

        echo "Color found\n";
        $newTile = new Imagick();

        // replace tile with color only
        $newTile->newImage($tileSize,$tileSize,"rgb($tileColor[0],$tileColor[1],$tileColor[2])");
        $stack->addImage($newTile);
        echo "Tile $x,$y Added...\n";
    }
}

$baseImg = new Imagick();
$baseImg->readImage(WORK_DIR . "test1.jpg");
$baseWidth = $baseImg->getImageWidth();
$baseHeight = $baseImg->getImageHeight();

$tileSize = 200;
$nbTilesX = ceil($baseWidth / $tileSize);
$nbTilesY = ceil($baseHeight / $tileSize);

$newWidth = $tileSize * $nbTilesX;
$newHeight = $tileSize * $nbTilesY;

$stack = new Imagick();

$pool = new Pool(16);

// vert
for ($y = 0; $y < $newHeight; $y += $tileSize){
    // hor
    for ($x = 0; $x < $newWidth; $x += $tileSize){
        $baseTile = $baseImg->getImageRegion($tileSize,$tileSize,$x,$y);
        echo "Tile $x,$y processing...\n";
        $pool->submit(new CreateTileTask($baseTile));
        while($pool->collect());
    }
}

$pool->shutdown();

$mosaic = $stack->montageImage(new ImagickDraw(), $nbTilesX . 'x' . $nbTilesY, $tileSize . 'x' . $tileSize, 0, '0');
$mosaic->cropImage($baseWidth, $baseHeight, 0, 0);

// Save img

$mosaic->setImageFormat("png");
$mosaic->setImageCompression(\Imagick::COMPRESSION_UNDEFINED);
$mosaic->setImageCompressionQuality(0);
$mosaic->writeImage(WORK_DIR . "mosaique.png");
?>