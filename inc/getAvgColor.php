<?php
// found on project https://github.com/eflorit/mosaic-generator/blob/master/Mosaic.php
// and adapted to avoid confusion with opacity
function getAvgColor($img) {
    
    $w = imagesx($img);
    $h = imagesy($img);

    // put a white background before calculating to treat transparency correctly
    $newImg = imagecreatetruecolor($w, $h);
    $background = imagecreate($w, $h);
    imagecolorallocate($background,255,255,255);
    imagecopy($newImg,$background,0,0,0,0,$w, $h);
    imagedestroy($background);
    imagecopy($newImg,$img,0,0,0,0,$w, $h);
    imagedestroy($img);
    
    $r = $g = $b = 0;
    for($y = 0; $y < $h; $y++) {
          for($x = 0; $x < $w; $x++) {
            $rgb = imagecolorat($newImg, $x, $y);
            $r += ($rgb >> 16);
            $g += ($rgb >> 8) & 0xFF;
            $b += $rgb & 0xFF;
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