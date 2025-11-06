<?php
// db_connection.php - Kết nối database

// Database configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', 'Haiquan1@');
define('DB_NAME', 'qlchitieu');
define('DB_PORT', 3306);

class DBConnection {
    private static $instance = null;
    private $conn;
    
    private function __construct() {
        try {
            $this->conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME, DB_PORT);
            
            if ($this->conn->connect_error) {
                error_log("Database connection error: " . $this->conn->connect_error);
                // Don't die, let the app continue and show error messages
                throw new Exception("Connection failed: " . $this->conn->connect_error);
            }
            
            $this->conn->set_charset("utf8mb4");
        } catch (Exception $e) {
            error_log("Database connection error: " . $e->getMessage());
            // Don't die, let the page render
        }
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getConnection() {
        return $this->conn;
    }
    
    // Chống clone
    private function __clone() {}
    
    // Chống unserialize
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

// Hàm tiện ích để lấy connection
function getDB() {
    $db = DBConnection::getInstance();
    $conn = $db->getConnection();
    if (!$conn || !is_object($conn) || !method_exists($conn, 'ping')) {
        throw new Exception("Database not connected");
    }
    if (!$conn->ping()) {
        throw new Exception("Database ping failed");
    }
    return $conn;
}

// Hàm kiểm tra kết nối
function testConnection() {
    try {
        $conn = getDB();
        return $conn->ping();
    } catch (Exception $e) {
        return false;
    }
}

// Hàm truy vấn an toàn
function query($sql, $params = []) {
    try {
        $conn = getDB();
        if (!$conn) {
            throw new Exception("No database connection");
        }
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            throw new Exception("Prepare failed: " . $conn->error);
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params)); // Mặc định string
            $stmt->bind_param($types, ...$params);
        }
        
        $stmt->execute();
        $result = $stmt->get_result();
        
        return $result;
    } catch (Exception $e) {
        error_log("Query error: " . $e->getMessage());
        // Return empty result set
        return new stdClass(); // Dummy object
    }
}

// Hàm query và fetch all
function queryAll($sql, $params = []) {
    try {
        $result = query($sql, $params);
        if (method_exists($result, 'fetch_all')) {
            return $result->fetch_all(MYSQLI_ASSOC);
        }
        return [];
    } catch (Exception $e) {
        error_log("QueryAll error: " . $e->getMessage());
        return [];
    }
}

// Hàm query và fetch one
function queryOne($sql, $params = []) {
    try {
        $result = query($sql, $params);
        if (method_exists($result, 'fetch_assoc')) {
            return $result->fetch_assoc();
        }
        return null;
    } catch (Exception $e) {
        error_log("QueryOne error: " . $e->getMessage());
        return null;
    }
}

// Hàm execute (INSERT, UPDATE, DELETE)
function execute($sql, $params = []) {
    try {
        $conn = getDB();
        if (!$conn) {
            return ['success' => false, 'affected_rows' => 0, 'insert_id' => 0];
        }
        $stmt = $conn->prepare($sql);
        
        if (!$stmt) {
            error_log("Execute prepare failed: " . $conn->error);
            return ['success' => false, 'affected_rows' => 0, 'insert_id' => 0];
        }
        
        if (!empty($params)) {
            $types = str_repeat('s', count($params));
            $stmt->bind_param($types, ...$params);
        }
        
        $success = $stmt->execute();
        $affected_rows = $stmt->affected_rows;
        $insert_id = $conn->insert_id;
        $stmt->close();
        
        return ['success' => $success, 'affected_rows' => $affected_rows, 'insert_id' => $insert_id];
    } catch (Exception $e) {
        error_log("Execute error: " . $e->getMessage());
        return ['success' => false, 'affected_rows' => 0, 'insert_id' => 0];
    }
}
?>

