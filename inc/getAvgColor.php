<?php
// found on project https://github.com/eflorit/mosaic-generator/blob/master/Mosaic.php
function getAvgColor($img) {
    
    $w = imagesx($img);
    $h = imagesy($img);
    
    $r = $g = $b = 0;
    for($y=0; $y<$h; $y++) {
          for($x=0; $x<$w; $x++) {
            $rgb = imagecolorat($img, $x, $y);
            $r += ($rgb >> 16) & 255;
            $g += ($rgb >> 8) & 255;
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