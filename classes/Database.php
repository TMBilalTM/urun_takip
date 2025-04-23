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
    
    /**
     * SQL sorgusunu parametreler ile çalıştırır
     * @param array $params Bağlanacak parametreler
     * @return bool Sorgu başarıyla çalıştırıldı mı
     */
    public function execute($params = []) {
        try {
            // Parametre yoksa doğrudan çalıştır
            if (empty($params)) {
                return $this->stmt->execute();
            }
            
            // SQL sorgusu içindeki named parametreleri bul
            $queryString = $this->stmt->queryString;
            preg_match_all('/:([a-zA-Z0-9_]+)/', $queryString, $matches);
            $placeholders = $matches[0] ?? [];
            
            if (empty($placeholders)) {
                // SQL sorgusunda hiç placeholder yoksa normal execute
                return $this->stmt->execute();
            }
            
            // Sadece SQL sorgusunda bulunan parametreleri işle
            $validParams = [];
            foreach ($placeholders as $placeholder) {
                $paramName = ltrim($placeholder, ':');
                
                // Parametre iki formatta da gelebilir - ':key' veya 'key'
                if (isset($params[$paramName])) {
                    $validParams[$placeholder] = $params[$paramName];
                } elseif (isset($params[':' . $paramName])) {
                    $validParams[$placeholder] = $params[':' . $paramName];
                }
            }
            
            // Debug bilgisi
            error_log("SQL: " . $queryString);
            error_log("Placeholders in query: " . implode(', ', $placeholders));
            error_log("Valid params: " . print_r($validParams, true));
            
            // İşlenmiş parametreleri kullanarak sorguyu çalıştır
            return $this->stmt->execute($validParams);
        } catch (PDOException $e) {
            error_log("PDO Execute Error: " . $e->getMessage());
            error_log("SQL Query: " . $this->stmt->queryString);
            error_log("Parameters: " . print_r($params, true));
            throw $e; // Hatayı yine de fırlat
        }
    }
    
    // Tüm kayıtları getirme - parametre geçişi eklendi
    public function resultSet($params = []) {
        try {
            // Parametreleri execute metoduna geçir
            $this->execute($params);
            return $this->stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("ResultSet hatası: " . $e->getMessage());
            error_log("SQL: " . $this->stmt->queryString);
            throw $e; // Hatayı yukarı fırlat
        }
    }
    
    // Tek kayıt getirme - parametre geçişi eklendi
    public function single($params = []) {
        try {
            // Parametreleri execute metoduna geçir
            $this->execute($params);
            return $this->stmt->fetch();
        } catch (PDOException $e) {
            error_log("Single hatası: " . $e->getMessage());
            error_log("SQL: " . $this->stmt->queryString);
            throw $e; // Hatayı yukarı fırlat
        }
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
