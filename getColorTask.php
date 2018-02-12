<?php
include 'getAvgColor.php';
class GetColorTask extends Threaded{    
    public function __construct(string $img){
        $this->imgPath = IMG_DIR . "/" . $img;
        $split = explode('.', $img);
        $this->imageId = $split[0];
        $this->imageExt = $split[1];
    }

    public function run(){      
        $pdo = $this->worker->getConnection();
        if (!$pdo) return;
        
        // statement for tbl_images insertion
        $stmt_Image = $pdo->prepare('INSERT INTO tbl_images(numero, fileExtension) VALUES(:numImg, :ext) ON DUPLICATE KEY UPDATE fileExtension = :ext');       
        // statement for tbl_colors insertion
        $stmt_Colors = $pdo->prepare('INSERT INTO tbl_colors(red, green, blue) VALUES (:red,:green,:blue) ON DUPLICATE KEY UPDATE red = :red');
        // statement for color id retrieving
        $stmt_ColorId = $pdo->prepare('SELECT numero FROM tbl_colors WHERE red = :red AND green = :green AND blue = :blue');       
        // statement for tbl_colorsinimages insertion
        $stmt_ColorsInImages = $pdo->prepare('INSERT INTO tbl_colorsinimages(num_tbl_images, num_tbl_colors, priority)' .
                        'VALUES(:numImg, :numColor, :priority)'
                        . ' ON DUPLICATE KEY UPDATE priority = :priority');
      
        // Get color
        $color = getAvgColor($this->imgPath);

        // Insert image
        $stmt_Image->bindValue(':numImg', $this->imageId, PDO::PARAM_INT);
        $stmt_Image->bindValue(':ext', $this->imageExt, PDO::PARAM_STR);  
        $stmt_Image->execute();

        // Insert each color...
        $priority = 1;
        $red = $color[0];
        $green = $color[1];
        $blue = $color[2];

        // Insert color in tbl_colors  
        $stmt_Colors->bindValue(':red', $red, PDO::PARAM_INT);
        $stmt_Colors->bindValue(':green', $green, PDO::PARAM_INT);
        $stmt_Colors->bindValue(':blue', $blue, PDO::PARAM_INT); 
        $stmt_Colors->execute();

        // Get color id
        $stmt_ColorId->bindValue(':red', $red, PDO::PARAM_INT);
        $stmt_ColorId->bindValue(':green', $green, PDO::PARAM_INT);
        $stmt_ColorId->bindValue(':blue', $blue, PDO::PARAM_INT);
        $stmt_ColorId->execute();
        $row = $stmt_ColorId->fetch(PDO::FETCH_OBJ);
        $colorId = "{$row->numero}";
                
        // Insert colors <-> images association
        $stmt_ColorsInImages->bindValue(':numImg', $this->imageId, PDO::PARAM_INT);
        $stmt_ColorsInImages->bindValue(':numColor', $colorId, PDO::PARAM_INT);
        $stmt_ColorsInImages->bindValue(':priority', $priority, PDO::PARAM_INT);
        $stmt_ColorsInImages->execute();

        echo "Image $this->imageId inserted.\n";
    }
}
?>