<?php
require_once '../lib/Database.php';
class Centers
{
    private $db;
    public function __construct()
    {
        $this->db = new Database;
    }
    public function getCenter($id)
    {
        $this->db->query('SELECT * FROM centers WHERE ID = :id AND Deleted = 0');
        $this->db->bind(':id',$id);
        $this->db->execute();
        if ($this->db->rowCount() === 0) {
            return false;
        }else{
            // return $this->db->stmt;
            return $this->db->single();
        }
    }
    public function deleteCenter($id)
    {
        $this->db->query('UPDATE centers SET Deleted = 1 WHERE ID=:id');
        $this->db->bind(':id',$id);
        $this->db->execute();
        return $this->db->rowCount();
    }
    public function getAllCenters()
    {
        $this->db->query('SELECT ID,
                                 UCASE(CenterName) as CenterName,
                                 Contact,
                                 Email
                          FROM   centers
                          WHERE  (Deleted = 0)');
        $this->db->execute();
        if ($this->db->rowCount() > 0) {
            return $this->db->stmt;
        }else{
            return false;
        }
    }
    public function createCenter($data)
    {
        $this->db->query('INSERT INTO centers (CenterName,Contact,Email,IsHead)
                          VALUES (:cname,:contact,:email,:ishead)');
        $this->db->bind(':cname',$data['centerName']);
        $this->db->bind(':contact',$data['contact']);
        $this->db->bind(':email',$data['email']);
        $this->db->bind(':ishead',$data['isHead']);
        if ($this->db->execute()) {
            return $this->db->dbh->lastInsertId();
        }else{
            return false;
        }
    }
}