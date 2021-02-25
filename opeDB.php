<?php
require_once('common.php');

class OpeDB {

    private $dbh;


    // private $userId = -1;
    // private $name = 'guest';
    // private $idm = '0000000000000000';
    // private $password = null;

    public function __construct() {
        $this->setDbh();
    }

    private function connectDB() {
        $dbh = new PDO('mysql:dbname=monitoring;host=localhost;charset=utf8', 'root', 'Fumiya_0324');
        $dbh->setAttribute(PDO::ATTR_ERRMODE,PDO::ERRMODE_EXCEPTION);
        return $dbh;
    }

    // dbh変数セッター----------------
    private function setDbh() {   
        $this->dbh = $this->connectDB();
    }

    protected function getDbh() {
        return $this->dbh;
    }
    // -------------------------------

    
    
}

?>