<?php
define("IMG_DIR","F:/img");
require_once('connectDb.php');
require_once('getColor.php');
use ColorThief\ColorThief;
// create pdo connection
$pdo = getConnection('mosaique');

// prepare all statements

// statement for tbl_images insertion
$stmt_Image = $pdo->prepare('INSERT INTO tbl_images(numero, fileExtension) VALUES(:numImg, :ext) ON DUPLICATE KEY UPDATE fileExtension = :ext');
$stmt_Image->bindParam(':numImg', $imageId, PDO::PARAM_INT);
$stmt_Image->bindParam(':ext', $imageExt, PDO::PARAM_STR);

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
$stmt_ColorsInImages->bindParam(':numImg', $imageId, PDO::PARAM_INT);
$stmt_ColorsInImages->bindParam(':numColor', $colorId, PDO::PARAM_INT);
$stmt_ColorsInImages->bindParam(':priority', $priority, PDO::PARAM_INT);

// parse all images
$numInserted = 0;
$dh = opendir(IMG_DIR);
if (!$dh) echo "Img directory not found";
while (($file = readdir($dh)) !== false){
    $path = IMG_DIR . "/" . $file;
    if (is_file($path)){
        $split = explode('.', $file);
        $imageId = $split[0];

        // test on a few images
        if ($numInserted > 50) break;

        $imageExt = $split[1];
        if (is_numeric($imageId)){
            // insert image
            $stmt_Image->execute();
            
            // evaluate colors in image
            $task = new GetColorTask($path);
            $task->run();
            $palette = $task->getPalette();

            $cnt = 1;
            foreach($palette as $color){
                $priority = $cnt++;
                $red = $color[0];
                $green = $color[1];
                $blue = $color[2];

                // add color in tbl_colors                     
                $stmt_Colors->execute();

                // get color id
                $stmt_ColorId->execute();
                $row = $stmt_ColorId->fetch(PDO::FETCH_OBJ);
                $colorId = "{$row->numero}";
                        
                // insert colors <-> images association
                $stmt_ColorsInImages->execute();
            }

            $numInserted ++;
            /* test values
            $red = rand(0,255);
            $green = rand(0,255);
            $blue = rand(0,255);
            $priority = 1;
            */

            
        }
    }
}
closedir($dh);
?>