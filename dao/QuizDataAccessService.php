<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:07
 */
class QuizDataAccessService {

    private $conn;

    public function __construct(PDOConn $PDOConn) {
        $this->conn = $PDOConn->connect();
    }

    public function getQuizFromCode(string $code) {
        $q = $this->conn->prepare("SELECT * FROM quiz WHERE invitation_code = :code");
        $q->bindParam(':code',$code,PDO::PARAM_INT);
        $q->execute();
        return $q->fetch();
    }

    public function create($postData) {
        $q = $this->conn->prepare(" INSERT INTO quiz (UUID,`date`,title,host,invitation_code)
              VALUES ((select uuid()),:date,:title,:host,:code)");
        $q->bindParam(':date',$postData['date'],PDO::PARAM_INT);
        $q->bindParam(':title',$postData['title'],PDO::PARAM_STR);
        $q->bindParam(':host',$postData['host'],PDO::PARAM_STR);
        $code = rand(10000,99999);
        $q->bindParam(':code',$code,PDO::PARAM_INT);
        if($q->execute()) {
            return $this->getQuizFromCode($code)->UUID;
        }
        return false;

    }

    public function getQuizzes() {
        $q = $this->conn->prepare('SELECT * FROM quiz');
        $q->execute();
        return $q->fetchAll();
    }

    public function getUserCreatedQuizzes(string $user) {
        $q = $this->conn->prepare("SELECT * FROM quiz WHERE host = :host ORDER BY quiz.updated DESC");
        $q->execute([':host' => $user]);
        return $q->fetchAll();
    }

    public function getQuizById(string $id) {
        $q = $this->conn->prepare("SELECT * FROM quiz WHERE UUID = :id");
        $q->bindParam(':id', $id,PDO::PARAM_STR);
        $q->execute();
        return $q->fetch();
    }
}