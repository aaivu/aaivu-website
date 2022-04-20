<?php

class Database
{
    public const HOST = "localhost";
    public const USERNAME = "root";
    public const PASSWORD = "";
    public const NAME = "aaivu-website";
}

abstract class ObjectPool
{
    private int $expirationTime;
    private array $locked, $unlocked;
    private array $objects;

    public function __construct()
    {
        $this->expirationTime = 30000; // 30 seconds
        $this->locked = array();
        $this->unlocked = array();
        $this->objects = array();
    }

    abstract protected function create(): Object;

    abstract public function validate(Object $o): bool;

    abstract public function expire(Object $o): void;

    public function checkOut(): Object
    {
        $now = time();
        if (count($this->unlocked) > 0) {
            foreach ($this->unlocked as $objId => $time) {
                $o = $this->objects[$objId];
                if (($now - $time) > $this->expirationTime) {
                    unset($this->unlocked[$objId]);
                    unset($this->objects[$objId]);
                    $this->expire($o);
                    $o = null;
                } else {
                    if ($this->validate($o)) {
                        unset($this->unlocked[$objId]);
                        $this->locked[$objId] = time();
                        return $o;
                    } else {
                        unset($this->unlocked[$objId]);
                        unset($this->objects[$objId]);
                        $this->expire($o);
                        $o = null;
                    }
                }
            }
        }
        $o = $this->create();
        $id = spl_object_hash($o);
        $this->objects[$id] = $o;
        $this->locked[$id] = time();
        return $o;
    }

    public function checkIn(Object $o): void
    {
        $id = array_search($o, $this->objects);
        unset($this->locked[$id]);
        $this->unlocked[$id] = time();
    }
}

interface IConnection
{
    public function connect(String $host, String $dbusername, String $dbpassword, String $dbname): void;
    public function is_valid(): bool;
    public function close(): void;
    public function query(String $query): mysqli_result|bool;
    public function multi_query(String $query): mysqli_result|bool;
    public function real_escape_string(String $string): String;
    public function prepare(String $query): mysqli_stmt|false;
}

class Connection implements IConnection
{
    private mysqli $connection;

    public function connect(String $host, String $dbusername, String $dbpassword, String $dbname): void
    {
        $this->connection = new mysqli($host, $dbusername, $dbpassword, $dbname);

        if ($this->connection->connect_errno) {
            echo "Failed to connect to MySQL: " . $this->connection->connect_error;
            exit();
        }
    }

    public function close(): void
    {
        $this->connection->close();
    }

    public function is_valid(): bool
    {
        if ($this->connection->connect_errno) {
            return false;
        }
        return true;
    }

    public function query(String $query): mysqli_result|bool
    {
        return $this->connection->query($query);
    }

    public function multi_query(String $query): mysqli_result|bool
    {
        return $this->connection->multi_query($query);
    }

    public function real_escape_string(String $string): String
    {
        return $this->connection->real_escape_string($string);
    }

    public function prepare(String $query): mysqli_stmt|false
    {
        return $this->connection->prepare($query);
    }
}

class ConnectionPool extends ObjectPool
{
    private String $host;
    private String $dbusername;
    private String $dbpassword;
    private String $dbname;
    private static ConnectionPool $connectionPool;

    private function __construct(String $host, String $dbusername, String $dbpassword, String $dbname)
    {
        parent::__construct();
        $this->host = $host;
        $this->dbusername = $dbusername;
        $this->dbpassword = $dbpassword;
        $this->dbname = $dbname;
    }

    public static function getInstance(): ConnectionPool
    {
        if (!isset(self::$connectionPool) || is_null(self::$connectionPool)) {
            self::$connectionPool = new ConnectionPool(Database::HOST, Database::USERNAME, Database::PASSWORD, Database::NAME);
        }
        return self::$connectionPool;
    }

    protected function create(): IConnection
    {
        $conn = new Connection();
        $conn->connect($this->host, $this->dbusername, $this->dbpassword, $this->dbname);
        return $conn;
    }

    public function validate(Object $conn): bool
    {
        return $conn->is_valid();
    }

    public function expire(Object $conn): void
    {
        $conn->close();
    }

    public function getConnection(): IConnection
    {
        return parent::checkOut();
    }

    public function releaseConnection(IConnection $conn): void
    {
        parent::checkIn($conn);
    }
}

class QueryExecutor
{
    private const QUERY = 0;
    private const MULTI_QUERY = 1;
    private const REAL_ESCAPE_STRING = 2;
    private const PREPARE = 3;

    private static function exe(String $query, int $type): mysqli_result|bool|String|mysqli_stmt
    {
        $connectionPool = ConnectionPool::getInstance();
        $connection = $connectionPool->getConnection();
        switch ($type) {
            case self::QUERY:
                $result = $connection->query($query);
                break;
            case self::MULTI_QUERY:
                $result = $connection->multi_query($query);
                break;
            case self::REAL_ESCAPE_STRING:
                $result = $connection->real_escape_string($query);
                break;
            case self::PREPARE:
                $result = $connection->prepare($query);
                break;
        }
        $connectionPool->releaseConnection($connection);
        return $result;
    }

    public static function query(String $query): mysqli_result|bool
    {
        return self::exe($query, self::QUERY);
    }

    public static function multi_query(String $query): mysqli_result|bool
    {
        return self::exe($query, self::MULTI_QUERY);
    }

    public static function real_escape_string(String $string): String
    {
        return self::exe($string, self::REAL_ESCAPE_STRING);
    }

    public static function prepare(String $query): mysqli_stmt|false
    {
        return self::exe($query, self::PREPARE);
    }
}
