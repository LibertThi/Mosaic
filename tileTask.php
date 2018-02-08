<?php
require_once 'vendor/autoload.php';
use ColorThief\ColorThief;
$imagine = new Imagine\Imagick\Imagine();

class TileTask extends Threaded{ 

    public function __construct(Imagick $baseImg, ){

    }

    public function run(){
        include 'vendor/autoload.php';
        $pdo = $this->worker->getConnection();
        if (!$pdo) return;
        
    }
}
?>