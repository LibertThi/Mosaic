<?php
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;

class GetColorTask extends Threaded{
    private $imageId;
    private $imageExt;
    private $img;
    private $quality;
    private $dominantColor;
    private $palette;
    private $paletteSize;
    
    public function __construct(string $img, int $quality = 50, int $paletteSize = 2){
        $this->img = IMG_DIR . "/" . $img;
        $split = explode('.', $img);
        $this->imageId = $split[0];
        $this->imageExt = $split[1];
        $this->quality = $quality;
        $this->paletteSize = $paletteSize;
    }

    public function getDominantColor(){
        return $this->dominantColor;
    }

    public function getPalette(){
        return $this->palette;
    }

    public function run(){
        include 'vendor/autoload.php';
        $pdo = $this->worker->getConnection();
        if (!$pdo) return;
        
        // statement for tbl_images insertion
        $stmt_Image = $pdo->prepare('INSERT INTO tbl_images(numero, fileExtension) VALUES(:numImg, :ext) ON DUPLICATE KEY UPDATE fileExtension = :ext');
        $stmt_Image->bindParam(':numImg', $this->imageId, PDO::PARAM_INT);
        $stmt_Image->bindParam(':ext', $this->imageExt, PDO::PARAM_STR);

        // statement for tbl_colors insertion
        $stmt_Colors = $pdo->prepare('INSERT INTO tbl_colors(red, green, blue) VALUES (:red,:green,:blue) ON DUPLICATE KEY UPDATE red = :red');
        $stmt_Colors->bindParam(':red', $red, PDO::PARAM_INT);
        $stmt_Colors->bindParam(':green', $green, PDO::PARAM_INT);
        $stmt_Colors->bindParam(':blue', $blue, PDO::PARAM_INT);

        // statement for color id retrieving
        $stmt_ColorId = $pdo->prepare('SELECT numero FROM tbl_colors WHERE red = :red AND green = :green AND blue = :blue');
        $stmt_ColorId->bindParam(':red', $red, PDO::PARAM_INT);
        $stmt_ColorId->bindParam(':green', $green, PDO::PARAM_INT);
        $stmt_ColorId->bindParam(':blue', $blue, PDO::PARAM_INT);

        // statement for tbl_colorsinimages insertion
        $stmt_ColorsInImages = $pdo->prepare('INSERT INTO tbl_colorsinimages(num_tbl_images, num_tbl_colors, priority)' .
                        'VALUES(:numImg, :numColor, :priority)'
                        . ' ON DUPLICATE KEY UPDATE priority = :priority');
        $stmt_ColorsInImages->bindParam(':numImg', $this->imageId, PDO::PARAM_INT);
        $stmt_ColorsInImages->bindParam(':numColor', $colorId, PDO::PARAM_INT);
        $stmt_ColorsInImages->bindParam(':priority', $priority, PDO::PARAM_INT);
      
        try{
            $this->palette = ColorThief::getPalette($this->img, $this->paletteSize, $this->quality);
            // TEST
            // $this->palette = array(array(1,2,3),array(4,5,6),array(7,8,9)); 
        }
        catch (Exception $e){
            echo "ERROR: Could not get palette from image $this->imageId. Removing it.\n";
            unlink($this->img);
            return;
        }      
       
        // Insert image
        $stmt_Image->execute();

        // Insert each color...
        $cnt = 1;
        foreach($this->palette as $color){
            $priority = $cnt++;
            $red = $color[0];
            $green = $color[1];
            $blue = $color[2];

            // Insert color in tbl_colors                     
            $stmt_Colors->execute();

            // Get color id
            $stmt_ColorId->execute();
            $row = $stmt_ColorId->fetch(PDO::FETCH_OBJ);
            $colorId = "{$row->numero}";
                    
            // Insert colors <-> images association
            $stmt_ColorsInImages->execute();
        }
        echo "Image $this->imageId inserted.\n";
    }
}
?>