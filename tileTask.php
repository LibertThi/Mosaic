<?php
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;
$imagine = new Imagine\Gd\Imagine();

class TileTask extends Threaded{ 
    public $data;

    public function __construct($data){
        $this->data = $data;
    }

    public function run(){
        include 'vendor/autoload.php';
        $pdo = $this->worker->getConnection();
        if (!$pdo) return;

        // Get the correct base tile
        $rect = array(
            'x'=> $this->data->x,
            'y' => $this->data->y,
            'width' => $this->data->tileSize,
            'height' => $this->data->tileSize);

        $baseTile = imagecrop($this->data->baseImg,$rect);
        
        // Get tile color
        $tileColor = ColorThief::getColor($baseTile,20);

        // Fetch the img from db with nearest dominant color
        $queryString =
        'SELECT tbl_images.numero, tbl_images.fileExtension, sqrt(pow(tbl_colors.red - :red, 2)+pow(tbl_colors.green - :green,2)+pow(tbl_colors.blue - :blue,2)) as ratio
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
        
        $this->data->imgId = $imgId;
        $this->data->imgExt = $imgExt;
        echo "Square " . $this->data->x . "/" . $this->data->y . " processed\n";
    }
}
?>