<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:07
 */
class PDOConn {

    private $conn;

    public function connect() {
        return $this->conn;
    }

    public function __construct() {
        try {

            $conn = new PDO("mysql:host=localhost;dbname=quizAtTheDicks;charset=utf8", 'root', '');
            $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);//remove for production
            $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES, true);
            $conn->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_OBJ);
            $this->conn = $conn;
        } catch (PDOException $e) {
            error_log($e->getMessage());
            echo $e->getMessage();
            die();
        }

    }
}