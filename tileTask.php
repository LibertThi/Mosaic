<?php
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;
$imagine = new Imagine\Gd\Imagine();

class TileTask extends Threaded{ 

    public function __construct($baseImg,int $offsetX, int $offsetY, int $tileSize){
        $this->baseImg = $baseImg;
        $this->x = $offsetX;
        $this->y = $offsetY;
        $this->tileSize = $tileSize;
    }

    public function run(){
        include 'vendor/autoload.php';
        $pdo = $this->worker->getConnection();
        if (!$pdo) return;
        echo "PDO OK\n";

        // Get the correct base tile
        $rect = array(
            'x'=> $this->x,
            'y' => $this->y,
            'width' => $this->tileSize,
            'height' => $this->tileSize);

        $baseTile = imagecrop($this->baseImg,$rect);
        
        // Get tile color
        $tileColor = ColorThief::getColor($baseTile,10);
        echo "COLOR OK\n";

        // Fetch the img from db with nearest dominant color
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
        $stmt->bindValue(':red',$tileColor[0],PDO::PARAM_INT);
        $stmt->bindValue(':green',$tileColor[1],PDO::PARAM_INT);
        $stmt->bindValue(':blue',$tileColor[2],PDO::PARAM_INT);
        $stmt->execute();

        $row = $stmt->fetch(PDO::FETCH_OBJ);
        $imgId = "{$row->numero}";
        $imgExt = "{$row->fileExtension}";

        echo "FETCH OK\n";
        
        $imagine = new Imagine\Gd\Imagine();
        $imageFromFile = $imagine->open(IMG_DIR . "/$imgId.$imgExt");
        $imageFromFile->resize(new Imagine\Image\Box($this->tileSize,$this->tileSize));
        //var_dump($imageFromFile);
       /* $point = new Imagine\Image\Point($this->x,$this->y);
        $this->mosaicImg->paste($imageFromFile,$point);*/
        // add tile to mosaic image
       /* 
        $mosaicImg->paste($imageFromFile,$point);*/
    }
}
?>