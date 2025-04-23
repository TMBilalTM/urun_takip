<?php
class Database {
    private $host = DB_HOST;
    private $user = DB_USER;
    private $pass = DB_PASS;
    private $dbname = DB_NAME;
    
    private $dbh;
    private $stmt;
    private $error;
    
    public function __construct() {
        // DSN oluşturma
        $dsn = 'mysql:host=' . $this->host . ';dbname=' . $this->dbname . ';charset=utf8';
        
        // PDO seçenekleri
        $options = array(
            PDO::ATTR_PERSISTENT => true,
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        );
        
        // PDO nesnesini oluşturma
        try {
            $this->dbh = new PDO($dsn, $this->user, $this->pass, $options);
        } catch(PDOException $e) {
            $this->error = $e->getMessage();
            echo 'Veritabanı Bağlantı Hatası: ' . $this->error;
        }
    }
    
    // Sorgu hazırlama
    public function query($sql) {
        $this->stmt = $this->dbh->prepare($sql);
    }
    
    // Sorgu parametresi bağlama
    public function bind($param, $value, $type = null) {
        if(is_null($type)) {
            switch(true) {
                case is_int($value):
                    $type = PDO::PARAM_INT;
                    break;
                case is_bool($value):
                    $type = PDO::PARAM_BOOL;
                    break;
                case is_null($value):
                    $type = PDO::PARAM_NULL;
                    break;
                default:
                    $type = PDO::PARAM_STR;
            }
        }
        
        $this->stmt->bindValue($param, $value, $type);
    }
    
    // Sorguyu çalıştırma
    public function execute() {
        return $this->stmt->execute();
    }
    
    // Tüm kayıtları getirme
    public function resultSet() {
        $this->execute();
        return $this->stmt->fetchAll();
    }
    
    // Tek kayıt getirme
    public function single() {
        $this->execute();
        return $this->stmt->fetch();
    }
    
    // Etkilenen satır sayısını getirme
    public function rowCount() {
        return $this->stmt->rowCount();
    }
    
    // Son eklenen kaydın ID'si
    public function lastInsertId() {
        return $this->dbh->lastInsertId();
    }
}
