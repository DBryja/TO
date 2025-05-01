<?php
class Database {
    private static $instance = null;
    private $conn;

    private function __construct() {
        // PostgreSQL connection details
        $host = getenv('DB_HOST') ?: 'postgres';
        $port = getenv('DB_PORT') ?: '5432';
        $dbname = getenv('DB_NAME') ?: 'wallet_app';
        $user = getenv('DB_USER') ?: 'postgres';
        $password = getenv('DB_PASSWORD') ?: 'postgres';

        try {
            $dsn = "pgsql:host=$host;port=$port;dbname=$dbname";
            $this->conn = new PDO($dsn, $user, $password);
            $this->conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            
            // Create tables if they don't exist
            $this->createTables();
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    // Prevent cloning of the instance
    private function __clone() {}
    
    // Prevent serialization of the instance
    public function __wakeup() {
        throw new Exception("Cannot deserialize singleton");
    }
    
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }
    
    public function getDb() {
        return $this->conn;
    }

    private function createTables() {
        // Existing code for creating tables
        $this->conn->exec("CREATE TABLE IF NOT EXISTS users (
            id SERIAL PRIMARY KEY,
            username VARCHAR(100) UNIQUE NOT NULL,
            password VARCHAR(255) NOT NULL
        )");

        // Create wallets table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS wallets (
            id SERIAL PRIMARY KEY,
            user_id INTEGER NOT NULL,
            FOREIGN KEY(user_id) REFERENCES users(id)
        )");

        // Create nominaly table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS nominaly (
            id SERIAL PRIMARY KEY,
            wallet_id INTEGER NOT NULL,
            nominal INTEGER NOT NULL,
            type VARCHAR(20) NOT NULL,
            count INTEGER NOT NULL,
            FOREIGN KEY (wallet_id) REFERENCES wallets(id),
            UNIQUE (wallet_id, nominal, type)
        )");

        // Create transactions table
        $this->conn->exec("CREATE TABLE IF NOT EXISTS transactions (
            id SERIAL PRIMARY KEY,
            wallet_id INTEGER NOT NULL,
            type VARCHAR(10) NOT NULL,
            amount NUMERIC(10,2) NOT NULL,
            timestamp TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            FOREIGN KEY (wallet_id) REFERENCES wallets(id)
        )");
    }
}