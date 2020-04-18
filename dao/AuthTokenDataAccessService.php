<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 19:30
 */
class AuthTokenDataAccessService {

    private $conn;

    public function __construct(PDOConn $PDOConn) {
        $this->conn = $PDOConn->connect();
    }

    public function checkQuizMasterCredentials(array $postData) {


        $username = $postData['username'];
        $q = $this->conn->prepare("SELECT * FROM master_creators WHERE username = :username");
        $q->bindParam(':username',$username,PDO::PARAM_STR);
        $q->execute();
        $row = $q->fetch();

        if(!isset($row)) return false;

        return password_verify($postData['password'],$row->password_hash) ? $row->UUID : false;
    }

    public function checkQuizMasterToken(string $token) {
        $q = $this->conn->prepare("SELECT * FROM master_creators WHERE UUID = :token");
        $q->bindParam(':token',$token,PDO::PARAM_STR);
        $q->execute();
        $row = $q->fetch();

        if(!isset($row)) return false;

        return true;
    }
}