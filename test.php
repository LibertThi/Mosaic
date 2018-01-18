<?php

define("IMG_DIR","F:/img");
require_once('connectDb.php');

// create pdo connection
$pdo = getConnection('mosaique');

// statement for tbl_images insertion
$stmt_Image = $pdo->prepare('INSERT INTO tbl_images(numero, fileExtension) VALUES(:num, :ext) ON DUPLICATE KEY UPDATE fileExtension = :ext');
$imageId;
$imageExt;
$stmt_Image->bindParam(':num', $imageId, PDO::PARAM_INT);
$stmt_Image->bindParam(':ext', $imageExt, PDO::PARAM_STR);

// parse all images
$numInserted = 0;
$dh = opendir(IMG_DIR);
if (!$dh) echo "Img directory not found";
while (($file = readdir($dh)) !== false){
    if (is_file(IMG_DIR . "/" . $file)){
        $split = explode('.', $file);
        $imageId = $split[0];
        $imageExt = $split[1];
        if (is_numeric($imageId)){
            $result = $stmt_Image->execute();
            if ($result){
                echo "Insert $file OK\n";
                $numInserted++;
            }
            else{
                echo "Insert $file FAILED\n";
            }
        }
    }
}
echo "Task ended : $numInserted images added/updated to db\n";
closedir($dh);
?>