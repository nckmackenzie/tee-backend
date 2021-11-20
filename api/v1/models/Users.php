<?php
require_once '../lib/Database.php';
class Users
{
    private $db;
    public function __construct()
    {
        $this->db = new Database;
    }
    public function getUser($id)
    {
        $this->db->query('SELECT u.ID,
                                 lcase(UserID) As UserID,
                                 ucase(UserName) As UserName,
                                 UserTypeId,
                                 ucase(t.UserType) As UserType,
                                 Active,
                                 CenterId,
                                 ucase(c.CenterName) As CenterName
                          FROM   users u inner join usertypes t on u.UserTypeId = t.ID
                                 inner join centers c on u.CenterId = c.ID
                          WHERE  (u.ID = :id) AND (u.Deleted = 0)');
        $this->db->bind(':id',$id);
        $this->db->execute();
        if ($this->db->rowCount() === 0) {
            return false;
        }else{
            return $this->db->single();
        }
    }
    public function checkUserIdExists($uid,$cid,$id)
    {
        $this->db->query('SELECT COUNT(ID) As uidCount
                          FROM   users 
                          WHERE  (UserID=:ui) AND (CenterId = :cid) AND (ID <> :id) AND Deleted=0');
        $this->db->bind(':ui',$uid);
        $this->db->bind(':cid',$cid);
        $this->db->bind(':id',$id);
        if ($this->db->getValue() > 0) {
            return true;
        }else{
            return false;
        }
    }
    public function createUser($data,$pwd)
    {
        $this->db->query('INSERT INTO users (UserID,UserName,`Password`,UserTypeId,CenterId)
                          VALUES(:userid,:uname,:pass,:utype,:center)');
        $this->db->bind(':userid',$data['userId']);
        $this->db->bind(':uname',$data['userName']);
        $this->db->bind(':pass', password_hash($pwd,PASSWORD_DEFAULT));
        $this->db->bind(':utype',$data['userType']);
        $this->db->bind(':center',$data['center']);
        if ($this->db->execute()) {
            return $this->db->dbh->lastInsertId();
        }else{
            return false;
        }
    }
    public function updateUser($data)
    {
        $qry = "";
        if ($data['nameChanged']) {
            $qry .= "UserName=:uname, ";
        }
        if ($data['userTypeChanged']) {
            $qry .= "UserTypeId=:utype, ";
        }
        if ($data['activeChanged']) {
            $qry .= "Active=:active, ";
        }
        $qry = rtrim($qry,', ');
        $sql = 'UPDATE users SET ' . $qry . ' WHERE ID=:id';
        $this->db->query($sql);
        if ($data['nameChanged']) {
            $this->db->bind(':uname',$data['userName']);
        }
        if ($data['userTypeChanged']) {
            $this->db->bind(':utype',$data['userType']);
        }
        if ($data['activeChanged']) {
            $this->db->bind(':active',$data['active']);
        }

        // return $sql;
         
        $data['nameChanged'] ? $this->db->bind(':uname',$data['userName']) : '';
        $data['userTypeChanged'] ? $this->db->bind(':utype',$data['userType']) : '';
        $data['activeChanged'] ? $this->db->bind(':active',$data['active']) : '';

        $this->db->bind(':id',$data['id']);
        $this->db->execute();
        if ($this->db->rowCount() === 0) {
            return false;
        }else{
            return $this->getUser($data['id']);
        }
    }
    public function deleteUser($id)
    {
        $this->db->query('UPDATE users SET Deleted = 1 WHERE ID = :id');
        $this->db->bind(':id',$id);
        if ($this->db->execute()) {
            return true;
        }else{
            return false;
        }
    }
    public function getAllUsers()
    {
        $this->db->query('SELECT u.ID,
                                 lcase(UserID) As UserID,
                                 ucase(UserName) As UserName,
                                 UserTypeId,
                                 ucase(t.UserType) As UserType,
                                 Active,
                                 CenterId,
                                 ucase(c.CenterName) As CenterName
                          FROM   users u inner join usertypes t on u.UserTypeId = t.ID
                                 inner join centers c on u.CenterId = c.ID
                          WHERE  (u.Deleted = 0)');
        
        $this->db->execute();
        if ($this->db->rowCount() === 0) {
            return false;
        }else{
            return $this->db->stmt;
        }
    }
    public function getAllUsersByCenter($cid)
    {
        $this->db->query('SELECT u.ID,
                                 lcase(UserID) As UserID,
                                 ucase(UserName) As UserName,
                                 ucase(t.UserType) As UserType,
                                 Active
                          FROM   users u inner join usertypes t on u.UserTypeId = t.ID
                          WHERE  (u.Deleted = 0) AND (CenterId = :cid)');
        $this->db->bind(':cid',$cid);
        $this->db->execute();
        if ($this->db->rowCount() === 0) {
            return false;
        }else{
            return $this->db->stmt;
        }
    }
}