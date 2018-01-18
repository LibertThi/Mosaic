<?php
define("IMG_DIR","F:/img");
$dh = opendir(IMG_DIR);

if (!$dh) echo "Img directory not found";
while (($file = readdir($dh)) !== false){
    $path = IMG_DIR . "/" . $file;
    if (is_file($path)){    
        print($path);
        print("\n");
    } 
}
?>