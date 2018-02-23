<?php
include 'inc/getAvgColor.php';
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
        $tileColor = getAvgColor($baseTile);

        // Fetch the img from db with nearest dominant color
       /* $queryString =
        "SELECT tbl_images.numero, tbl_images.fileExtension, sqrt(pow((tbl_colors.red - $tileColor[0]) * 0.650, 2)+pow((tbl_colors.green - $tileColor[1]) * 0.794,2)+pow((tbl_colors.blue - $tileColor[2]) * 0.557,2)) as ratio
            FROM tbl_images
            JOIN tbl_colors
                ON tbl_images.num_tbl_colors = tbl_colors.numero
            ORDER BY ratio 
            LIMIT 1";*/
        
        // increases offset if no result is found
        $row = false;
        $offset = 5;

        $queryString =
            "SELECT tbl_images.numero, tbl_images.fileExtension
                FROM tbl_images
                JOIN tbl_colors
                    ON tbl_images.num_tbl_colors = tbl_colors.numero
                WHERE tbl_colors.red BETWEEN (:red - :offset) AND (:red + :offset)
                AND tbl_colors.green BETWEEN (:green - :offset) AND (:green + :offset)
                AND tbl_colors.blue BETWEEN (:blue - :offset) AND (:blue + :offset)
                LIMIT 1";
        $stmt = $pdo->prepare($queryString);
        $stmt->bindParam(':red', $tileColor[0],PDO::PARAM_INT);
        $stmt->bindParam(':green', $tileColor[1],PDO::PARAM_INT);
        $stmt->bindParam(':blue', $tileColor[2],PDO::PARAM_INT);
        $stmt->bindParam(':offset', $offset,PDO::PARAM_INT);

        while ($row == false and $offset <= 130) {
            $stmt->execute();
            $row = $stmt->fetch(PDO::FETCH_OBJ);
            $offset += 5;
        }
        //echo "$offset\n";

        if ($row == false){
            echo "Error with query.\n";
            exit;
        }

        $imgId = "{$row->numero}";
        $imgExt = "{$row->fileExtension}";
        
        $this->data->imgId = $imgId;
        $this->data->imgExt = $imgExt;
        echo "Tile " . $this->data->x . "x" . $this->data->y . " done\n";
    }
}
?>