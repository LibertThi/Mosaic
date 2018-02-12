<?php
include 'getAvgColor.php';
class GetColorTask extends Threaded{    
    public function __construct(string $img){
        $this->imgPath = IMG_DIR . "/" . $img;
        $split = explode('.', $img);
        $this->imgId = (int) $split[0];
        $this->imgExt = (string) $split[1];
    }

    public function run(){      
        $pdo = $this->worker->getConnection();
        if (!$pdo) return;   
      
        // Get color
        $norm_ext = mb_strtoupper($this->imgExt);
        switch ($norm_ext){
            case 'GIF':
                $img = imagecreatefromgif($this->imgPath);
                break;
            case 'JPG':
            case 'JPEG':
                $img = imagecreatefromjpeg($this->imgPath);
                break;                
            case 'BMP':
                $img = imagecreatefrombmp($this->imgPath);
                break;
            case 'PNG':
            default:
                $img = imagecreatefrompng($this->imgPath);
                break;
        }

        // eval average color
        $color = getAvgColor($img);

        // Insert each color...
        $red = $color[0];
        $green = $color[1];
        $blue = $color[2];

        // Insert color in tbl_colors  
        $result = $pdo->query("INSERT INTO tbl_colors(red, green, blue)
                                    VALUES ($red,$green,$blue)
                                    ON DUPLICATE KEY UPDATE red = $red");

        // Get color id
        $result = $pdo->query("SELECT numero FROM tbl_colors
                                    WHERE red = $red AND green = $green AND blue = $blue LIMIT 1");
        $row = $result->fetch(PDO::FETCH_OBJ);
        $colorId = (int) "{$row->numero}";

        // Insert image
        $stmt = $pdo->prepare("INSERT INTO tbl_images(numero,fileExtension,num_tbl_colors)
                                    VALUES(:imgId,:imgExt,:colorId)
                                    ON DUPLICATE KEY UPDATE num_tbl_colors = :colorId");
        $stmt->bindValue(':imgId',$this->imgId,PDO::PARAM_INT);
        $stmt->bindValue(':imgExt',$this->imgExt,PDO::PARAM_STR);
        $stmt->bindValue(':colorId',$colorId,PDO::PARAM_INT);
        $stmt->execute();

        echo "Image $this->imgId inserted.\n";
    }
}
?>