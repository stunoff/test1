<?php

namespace App\Repository;

use App\Core\DB\DBInstance;
use PDO;
use PDOStatement;

abstract class AbstractRepository
{
    /**
     * @var \PDO
     */
    protected $db = null;
    
    public function __construct()
    {
        $this->db = $this->getConnection();
    }
    
    /**
     * @return \PDO
     */
    protected function getConnection()
    {
        return DBInstance::getInstance()->getConnection();
    }
    
    protected function fetchAll(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    
    protected function fetchKeyPair(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetchAll(PDO::FETCH_KEY_PAIR);
    }
    
    protected function fetchOne(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    protected function fetchField(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $stmt->fetchColumn();
    }
    
    protected function insert(PDOStatement $stmt)
    {
        $stmt->execute();
        
        return $this->db->lastInsertId();
    }
    
    protected function exec(PDOStatement $stmt)
    {
        return $stmt->execute();
    }
}
