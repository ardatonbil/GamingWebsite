<?php
// Database Configuration for CS306 Project - Gaming Database

// Load MongoDB library if available
if (file_exists('vendor/autoload.php')) {
    require_once 'vendor/autoload.php';
} elseif (file_exists(_DIR_ . '/../vendor/autoload.php')) {
    require_once _DIR_ . '/../vendor/autoload.php';
}

// MySQL Configuration
class MySQLConnection {
    private $host = "localhost";
    private $username = "root";
    private $password = "";
    private $database = "GamingDatabase";    // ← CHANGED from cs306_project
    private $port = "3306"; // Change to 3307 if you changed MySQL port
    public $connection;
    
    public function __construct() {
        try {
            $this->connection = new mysqli(
                $this->host, 
                $this->username, 
                $this->password, 
                $this->database,
                $this->port
            );
            
            if ($this->connection->connect_error) {
                throw new Exception("MySQL Connection failed: " . $this->connection->connect_error);
            }
            
            // Set charset to UTF-8
            $this->connection->set_charset("utf8");
            
        } catch (Exception $e) {
            die("Database connection error: " . $e->getMessage());
        }
    }
    
    public function getConnection() {
        return $this->connection;
    }
    
    public function close() {
        if ($this->connection) {
            $this->connection->close();
        }
    }
}

// MongoDB Configuration
class MongoDBConnection {
    private $uri = "mongodb://localhost:27017";
    private $database_name = "GamingDatabase";    // ← CHANGED from cs306_project
    public $client;
    public $database;
    
    public function __construct() {
        try {
            // Check if MongoDB\Client class exists
            if (!class_exists('MongoDB\Client')) {
                throw new Exception("MongoDB PHP library not found. Please run 'composer require mongodb/mongodb' in the project directory.");
            }
            
            $this->client = new MongoDB\Client($this->uri);
            $this->database = $this->client->selectDatabase($this->database_name);
        } catch (Exception $e) {
            die("MongoDB connection error: " . $e->getMessage());
        }
    }
    
    public function getDatabase() {
        return $this->database;
    }
    
    public function getCollection($collection_name) {
        return $this->database->selectCollection($collection_name);
    }
}

// Helper function to get MySQL connection
function getMySQLConnection() {
    $db = new MySQLConnection();
    return $db->getConnection();
}

// Helper function to get MongoDB database
function getMongoDatabase() {
    $db = new MongoDBConnection();
    return $db->getDatabase();
}

// Helper function to get MongoDB collection
function getMongoCollection($collection_name) {
    $db = new MongoDBConnection();
    return $db->getCollection($collection_name);
}
?>