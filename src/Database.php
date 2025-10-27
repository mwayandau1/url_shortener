<?php

/**
 * Database connection and query management class
 */
class Database
{
    private PDO $connection;
    private array $config;

    /**
     * Constructor
     * 
     * @param array $config Database configuration array
     */
    public function __construct(array $config)
    {
        $this->config = $config;
        $this->connect();
    }

    /**
     * Establish database connection
     * 
     * @return void
     * @throws Exception If connection fails
     */
    private function connect(): void
    {
        $dsn = "mysql:host={$this->config['host']};dbname={$this->config['database']};charset={$this->config['charset']}";
        
        try {
            $this->connection = new PDO($dsn, $this->config['username'], $this->config['password'], $this->config['options']);
        } catch (PDOException $e) {
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }

    /**
     * Get the PDO connection instance
     * 
     * @return PDO The database connection
     */
    public function getConnection(): PDO
    {
        return $this->connection;
    }

    /**
     * Execute a prepared SQL query
     * 
     * @param string $sql SQL query with placeholders
     * @param array $params Parameters for the query
     * @return PDOStatement The executed statement
     */
    public function query(string $sql, array $params = []): PDOStatement
    {
        $stmt = $this->connection->prepare($sql);
        $stmt->execute($params);
        return $stmt;
    }

    /**
     * Fetch a single row from the database
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array|null The fetched row or null if not found
     */
    public function fetchOne(string $sql, array $params = []): ?array
    {
        $stmt = $this->query($sql, $params);
        $result = $stmt->fetch();
        return $result ?: null;
    }

    /**
     * Fetch all rows from the database
     * 
     * @param string $sql SQL query
     * @param array $params Query parameters
     * @return array Array of fetched rows
     */
    public function fetchAll(string $sql, array $params = []): array
    {
        $stmt = $this->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Insert data into a table
     * 
     * @param string $table Table name
     * @param array $data Associative array of column => value pairs
     * @return int The inserted record's ID
     */
    public function insert(string $table, array $data): int
    {
        $columns = implode(',', array_keys($data));
        $placeholders = ':' . implode(', :', array_keys($data));
        
        $sql = "INSERT INTO {$table} ({$columns}) VALUES ({$placeholders})";
        $this->query($sql, $data);
        
        return (int) $this->connection->lastInsertId();
    }
}