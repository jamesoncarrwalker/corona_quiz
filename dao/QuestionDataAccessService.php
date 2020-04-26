<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:12
 */
class QuestionDataAccessService {

    private $conn;

    public function __construct(PDOConn $PDOConn) {
        $this->conn = $PDOConn->connect();
    }

    public function getAllQuestionsForQuiz(string $quiz) {
        $q = $this->conn->prepare("SELECT * FROM question WHERE quiz_UUID = :id order by sort_order");
        $q->bindParam(':id',$quiz,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll();
    }

    public function getQuestionsForRound(string $quiz, string $round) {
        $q = $this->conn->prepare("SELECT * FROM question
WHERE quiz_UUID = :id
AND round = :round
ORDER BY sort_order ASC
");
        $q->bindParam(':id',$quiz,PDO::PARAM_STR);
        $q->bindParam(':round',$round,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll();
    }

    public function getTotalQuestionsForRound(string $quiz, string $round) {
        $q = $this->conn->prepare("SELECT COUNT(UUID) AS total FROM question WHERE quiz_UUID = :id AND round = :round");
        $q->bindParam(':id',$quiz,PDO::PARAM_STR);
        $q->bindParam(':round',$round,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchColumn();
    }

    public function addQuestions(string $quizId, string $round, array $questions,array $points) {
        $sql = "INSERT INTO question (UUID,quiz_UUID, round, title, points, sort_order) VALUES ";
        $bindArray = [':quiz' => $quizId,':round'=> $round];
        $values = [];
        $i = $this->getTotalQuestionsForRound($quizId,$round);
        foreach($questions as $key => $question) {
            $values[] = '((SELECT uuid()), :quiz, :round, :title_' . $key . ', :points_' . $key . ', :sort_order_' . $key . ')';
            $bindArray[':title_' . $key] = $question;
            $bindArray[':sort_order_' . $key] = $i;
            $bindArray[':points_' . $key] = $points[$key]??1;
            $i++;
        }
        $sql = $sql . implode(',',$values);
        $q = $this->conn->prepare($sql);
        return $q->execute($bindArray);
    }
    public function addQuestion(string $quizId, string $round, string $question, int $sort_order) {
        $q = $this->conn->prepare("INSERT INTO question (UUID,quiz_UUID, round, title, points)
                                    VALUES ((SELECT uuid()), :quiz,:round,:title,0,:sort_order))");
        return $q->execute([':quiz' => $quizId,':round' => $round,':title' => $question,':sort_order' => $sort_order]);
    }



}