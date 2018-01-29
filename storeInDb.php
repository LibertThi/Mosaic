<?php
define("IMG_DIR","F:/img");
require_once("dbWorker.php");
require_once('getColorTask.php');

// pool
$pool = new Pool(16, 'Connection', ["root","","mosaique"]);

// parse all images
$dh = opendir(IMG_DIR);
if (!$dh) echo "Img directory not found";
while (($file = readdir($dh)) !== false){
    $path = IMG_DIR . "/" . $file;
    if (is_file($path)){
        $split = explode('.', $file);
        $imageId = $split[0];
        
        if (is_numeric($imageId)){
            $pool->submit(new GetColorTask($file,50,2));           
        }
    }
}

closedir($dh);
while($pool->collect());
$pool->shutdown();

?>