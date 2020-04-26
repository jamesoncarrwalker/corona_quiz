<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 22:44
 */
class RoundDataAccessService {

    private $conn;

    public function __construct(PDOConn $conn) {
        $this->conn = $conn->connect();
    }

    public function getRoundById(string $round,string $quiz) {
        $q = $this->conn->prepare('SELECT * FROM round WHERE quiz_UUID = :quiz AND UUID = :round');
        $q->bindParam(':quiz',$quiz, PDO::PARAM_STR);
        $q->bindParam(':round',$round, PDO::PARAM_STR);
        $q->execute();
        return $q->fetch();
    }

    public function getAllRoundForQuiz(string $quizId) {
        $q = $this->conn->prepare('SELECT * FROM round WHERE quiz_UUID = :id');
        $q->bindParam(':id',$quizId, PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll();
    }

    public function addRounds(string $quizId, array $rounds) {

        $sql = "INSERT INTO round (UUID,quiz_UUID, title, sort_order) VALUES ";
        $bindArray = [':quiz' => $quizId];
        $values = [];
        $i = 0;
        foreach($rounds as $key => $round) {
            $values[] = '((SELECT uuid()), :quiz, :title_' . $key . ', :sort_order_' . $key . ')';
            $bindArray[':title_' . $key] = $round;
            $bindArray[':sort_order_' . $key] = $i;
            $i++;
        }
        $sql = $sql . implode(',',$values);
        $q = $this->conn->prepare($sql);
        return $q->execute($bindArray);
    }

    public function setRoundQuestionVisibility(string $quiz,string $round, bool $show) {
        $q = $this->conn->prepare(" UPDATE round
                                    SET show_answers = :show
                                    WHERE round.UUID = :round
                                    AND round.quiz_UUID = :quiz");
        $q->bindParam(':quiz',$quiz,PDO::PARAM_STR);
        $q->bindParam(':round',$round,PDO::PARAM_STR);
        $q->bindParam(':show',$show,PDO::PARAM_BOOL);
        return $q->execute();
    }

}