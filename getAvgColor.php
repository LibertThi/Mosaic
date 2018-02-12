<?php
function getAvgColor($imgPath) {
    $type = exif_imagetype($imgPath);
    switch ($type){
        case IMAGETYPE_GIF:
            $img = imagecreatefromgif($imgPath);
            break;
        case IMAGETYPE_JPEG:
            $img = imagecreatefromjpeg($imgPath);
            break;                
        case IMAGETYPE_BMP:
            $img = imagecreatefrombmp($imgPath);
            break;
        case IMAGETYPE_PNG:
        default:
            $img = imagecreatefrompng($imgPath);
            break;
    }
    $w = imagesx($img);
    $h = imagesy($img);
    
    $r = $g = $b = 0;
    for($y=0; $y<$h; $y++) {
          for($x=0; $x<$w; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            $r += $rgb >> 16;
            $g += $rgb >> 8 & 255;
            $b += $rgb & 255;
        }
    }
    
    $pxls = $w * $h;
    
    return array(
        round($r / $pxls),
        round($g / $pxls),
        round($b / $pxls)
    );
}
?>