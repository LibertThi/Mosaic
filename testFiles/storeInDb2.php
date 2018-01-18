<?php

define("IMG_DIR","F:/img");

class ConnectWorker extends Worker {  
    protected $dsn;
    protected $user;
    protected $pass;
    protected static $pdo;

    public function __construct($host, $user, $pass, $db) {
        $this->dsn = 'mysql:dbname=' . $db . ';host=' . $host;
        $this->user = $user;
        $this->pass = $pass;
    }

    public function run(){
        self::$pdo = new PDO(
            $this->dsn,$this->user,$this->pass);
    }
    
    public function getConnection() {
        return self::$pdo;
    }       
}

/*class InsertQuery extends Threaded {
    protected $sourceImage;
    protected $color;
    protected $priority;
    protected $result;

    public function __construct(
        string $sourceImage, array $color, int $priority, Threaded $store) {
        
        $this->sourceImage = $sourceImage;
        $this->color = $color;
        $this->priority = $priority;
        $this->result = $store;
    }
    
    public function run() {
        $pdo = $this->worker->getConnection();
        
        $split = explode('.',  $this->sourceImage);
        $imageId = $split[0];
        $imageExt = $split[1];
        $stmtInsertImage = $pdo->prepare('INSERT INTO tbl_images(numero, fileExtension) VALUES(:id, :ext) ON DUPLICATE KEY UPDATE fileExtension = :ext');
        $stmtInsertImage->bindValue(':id', $imageId, PDO::PARAM_INT);
        $stmtInsertImage->bindValue(':ext', $imageExt, PDO::PARAM_STR);

        $result = $stmtInsertImage->execute();

        if ($result) {
            echo "Insert of image $this->sourceImage successful\n";
        }
        else{
            echo "Insert of image $this->sourceImage failed\n";
        }
    }  
}*/

class PDOTask extends Threaded{
    protected $query;

    public function __construct(string $queryString){
        $this->query = $queryString;
    }

    public function run(){
        $pdo = $this->worker->getConnection();
        if ($pdo){
            $result = $pdo->query($this->sql);
            echo "done\n";
        }
    }
}



$pool = new Pool(8, "ConnectWorker", ["localhost", "root", "", "mosaique"]);

$dh = opendir(IMG_DIR);

if (!$dh) echo "Img directory not found";
while (($file = readdir($dh)) !== false){
    if (is_file(IMG_DIR . "/" . $file)){
        $split = explode('.',  $file);
        $imageId = $split[0];
        $imageExt = $split[1];
        $pool->submit(new PDOTask("INSERT INTO tbl_images(numero, fileExtension) VALUES($imageId, $imageExt) ON DUPLICATE KEY UPDATE fileExtension = $imageExt"));        
    }   
}
$pool->shutdown(); // shutdown the pool to make sure it has completely finished executing

?>