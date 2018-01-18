<?php
function getConnection($dbName){
    $dsn = 'mysql:dbname=' . $dbName . ';host=localhost';
    $user = 'root';
    $password = '';
    try {
        $cnn = new PDO($dsn, $user, $password);
        $cnn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        $cnn->exec("SET CHARACTER SET utf8");	
        return $cnn;
    }
    catch (PDOException $e) {
        print "Connection failed : {$e->getMessage()}";
        return null;
    }
}
?>