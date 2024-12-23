<?php
// Prevent direct access to this file
if (!defined('BASEPATH')) {
    exit('No direct script access allowed');
}
error_log("Debugging __wakeup in Database class");


class Database {
    private $host;
    private $username;
    private $password;
    private $database;
    private $connection;
    private static $instance = null;

    private function __construct() {
        $this->host = DB_HOST;
        $this->username = DB_USER;
        $this->password = DB_PASS;
        $this->database = DB_NAME;
        $this->connect();
    }
    

    // Singleton pattern implementation
    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    // Create database connection
    private function connect() {
        try {
            $this->connection = new PDO(
                "mysql:host={$this->host};dbname={$this->database};charset=utf8mb4",
                $this->username,
                $this->password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
        } catch (PDOException $e) {
            die("Connection failed: " . $e->getMessage());
        }
    }

    // Get database connection
    public function getConnection() {
        return $this->connection;
    }

    // Execute query with parameters
    public function query($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt;
        } catch (PDOException $e) {
            error_log("Query failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Fetch single row
    public function fetchOne($sql, $params = []) {
        try {
            $stmt = $this->connection->prepare($sql);
            $stmt->execute($params);
            return $stmt->fetch();
        } catch (PDOException $e) {
            error_log("FetchOne failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Fetch all rows
    public function fetchAll($sql, $params = []) {
        try {
            $stmt = $this->query($sql, $params);
            return $stmt->fetchAll();
        } catch (PDOException $e) {
            error_log("FetchAll failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Insert record and return last insert ID
    public function insert($table, $data) {
        try {
            $fields = array_keys($data);
            $values = array_fill(0, count($fields), '?');
            
            $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") 
                    VALUES (" . implode(', ', $values) . ")";
            
            $this->query($sql, array_values($data));
            return $this->connection->lastInsertId();
        } catch (PDOException $e) {
            error_log("Insert failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Update records
    public function update($table, $data, $where, $whereParams = []) {
        try {
            $fields = array_keys($data);
            $set = array_map(function($field) {
                return "$field = ?";
            }, $fields);
            
            $sql = "UPDATE $table SET " . implode(', ', $set) . " WHERE $where";
            
            $params = array_merge(array_values($data), $whereParams);
            $this->query($sql, $params);
            
            return true;
        } catch (PDOException $e) {
            error_log("Update failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Delete records
    public function delete($table, $where, $params = []) {
        try {
            $sql = "DELETE FROM $table WHERE $where";
            $this->query($sql, $params);
            return true;
        } catch (PDOException $e) {
            error_log("Delete failed: " . $e->getMessage());
            throw $e;
        }
    }

    // Begin transaction
    public function beginTransaction() {
        return $this->connection->beginTransaction();
    }

    // Commit transaction
    public function commit() {
        return $this->connection->commit();
    }

    // Rollback transaction
    public function rollback() {
        return $this->connection->rollBack();
    }

    // Prevent cloning of the instance
    private function __clone() {}

    // Prevent unserialize of the instance
    public function __wakeup() {}

    // Test koneksi database
    public static function testConnection() {
        try {
            $db = self::getInstance();
            echo "Database connected successfully!";
            return true;
        } catch (Exception $e) {
            echo "Connection failed: " . $e->getMessage();
            return false;
        }
    }
}
