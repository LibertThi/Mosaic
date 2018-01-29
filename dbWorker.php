<?php
class Connection extends Worker {   
    protected static $link;
     
    public function __construct($username, $password, $database) {
        $this->username = $username;
        $this->password = $password;
        $this->database = $database;
    }    
    public function getConnection() {
        if(!self::$link) {
            $dsn = 'mysql:dbname=' . $this->database . ';host=localhost';
            echo 'Thread: '. $this->getThreadId() ." Connecting to db\n";
            self::$link = new PDO($dsn,$this->username,$this->password);
        }       
        return self::$link;
    }   
}

?>