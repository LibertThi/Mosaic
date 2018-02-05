<?php
require_once 'connectDb.php';

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
$stmt->bindParam(':red',$red,PDO::PARAM_INT);
$stmt->bindParam(':green',$green,PDO::PARAM_INT);
$stmt->bindParam(':blue',$blue,PDO::PARAM_INT);

$red = 10;
$green = 100;
$blue = 25;

$stmt->execute();
$row = $stmt->fetch(PDO::FETCH_OBJ);
$imgId = "{$row->numero}";
$imgExt = "{$row->fileExtension}";
echo "$imgId.$imgExt";
?>