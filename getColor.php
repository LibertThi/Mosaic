<?php
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;

/*$sourceImage = "F:/img/657.png";

$area = [];
$area['x'] = 40;
$area['y'] = 40;
$area['w'] = 10;
$area['h'] = 10;

$dominantColor = ColorThief::getColor($sourceImage, 1, $area);

echo "Couleur dominante:\n";
echo "rgb($dominantColor[0],$dominantColor[1],$dominantColor[2])";*/

class GetColorTask extends Threaded{
    private $img;
    private $quality;
    private $dominantColor;
    private $palette;
    private $paletteSize;
    
    public function __construct(string $img, int $quality = 5, int $paletteSize = 2){
        $this->img = $img;
        $this->quality = $quality;
        $this->paletteSize = $paletteSize;
    }

    public function getDominantColor(){
        return $this->dominantColor;
    }

    public function getPalette(){
        return $this->palette;
    }

    public function run(){
        include 'vendor/autoload.php';
        $this->palette = ColorThief::getPalette($this->img, $this->paletteSize, $this->quality);
        //$this->dominantColor = ColorThief::getColor($this->img, $this->quality);
        /*print_r($this->img);
        print_r($this->dominantColor);*/
    }
}

/*$task = new GetColorTask("F:/img/10001.png", 10);
$task->run();
print_r($task->getDominantColor());
print_r($task->getPalette());*/


/*$pool = new Pool(16);

$dh = opendir(IMG_DIR);

if (!$dh) echo "Img directory not found";
while (($file = readdir($dh)) !== false){
    $path = IMG_DIR . "/" . $file;
    if (is_file($path)){    
        $pool->submit(new GetColorTask($path, 10)); 
    } 
}
$pool->collect();
$pool->shutdown();
closedir($dh);*/
?>