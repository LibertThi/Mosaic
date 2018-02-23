<?php
define("IMG_DIR","img");
require_once("inc/dbWorker.php");
require_once('inc/getColorTask.php');

// pool
$pool = new Pool(50, 'Connection', ["root","","mosaic"]);

// parse all images
$dh = opendir(IMG_DIR);
if (!$dh) echo "Img directory not found";
while (($file = readdir($dh)) !== false){
    $path = IMG_DIR . "/" . $file;
    if (is_file($path)){
        $split = explode('.', $file);
        $imageId = $split[0];
        
        if (is_numeric($imageId)){
            $pool->submit(new GetColorTask($file));     
            while($pool->collect());
        }
    }
}
$pool->shutdown();
closedir($dh);
?>