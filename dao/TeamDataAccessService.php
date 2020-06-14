<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 31/03/2020
 * Time: 11:09
 */
class TeamDataAccessService {

    private $conn;

    public function __construct(PDOConn $PDOConn) {
        $this->conn = $PDOConn->connect();
    }

    public function getTeamByName(string $name,string $quiz) {
        $q = $this->conn->prepare("SELECT * FROM team WHERE team_name = :name AND quiz_UUID = :quiz");
        $q->bindParam(':name',$name,PDO::PARAM_STR);
        $q->bindParam(':quiz',$quiz,PDO::PARAM_STR);
        $q->execute();
        return $q->fetch();
    }

    public function getTeamById(string $name,string $quiz) {
        $q = $this->conn->prepare("SELECT * FROM team WHERE UUID = :name AND quiz_UUID = :quiz");
        $q->bindParam(':name',$name,PDO::PARAM_STR);
        $q->bindParam(':quiz',$quiz,PDO::PARAM_STR);
        $q->execute();
        return $q->fetch();
    }

    public function create(string $name,string $quiz) {
        $q = $this->conn->prepare("INSERT INTO team (UUID, quiz_UUID, team_name) VALUES ((SELECT uuid()),:quiz,:name)");
        $q->bindParam(':name',$name,PDO::PARAM_STR);
        $q->bindParam(':quiz',$quiz,PDO::PARAM_STR);
        return $q->execute();

    }

    public function getTeamsForQuiz(string $quiz) {
        $q = $this->conn->prepare("SELECT team.UUID, team.* FROM team WHERE quiz_UUID = :quiz");
        $q->execute([':quiz' => $quiz]);
        return $q->fetchAll(PDO::FETCH_UNIQUE);
    }

    public function getTeamPointsForQuizByRound(string $quiz, string $team) {
        $q = $this->conn->prepare(" SELECT round, SUM(IF(answer.points > 0,answer.points,0)) AS points
                                    FROM answer
                                    INNER JOIN question ON
                                      (question.quiz_UUID = answer.quiz_UUID AND question.UUID = answer.question_UUID)
                                    WHERE answer.team_UUID = :team
                                    AND answer.quiz_UUID = :quiz
                                    GROUP BY question.round
                                    ");
        $q->execute([':team' => $team,':quiz' => $quiz]);
        return $q->fetchAll(PDO::FETCH_KEY_PAIR);
    }

    public function getTeamsForMarksheets(string $quizId, string $roundId) {
        $q = $this->conn->prepare("SELECT DISTINCT(team_UUID)
                                   FROM answer
                                   INNER JOIN question ON (question.quiz_UUID = answer.quiz_UUID)
                                   WHERE answer.quiz_UUID = :quiz
                                   AND question.round = :round
                                    ");
        $q->execute([':round' => $roundId,':quiz' => $quizId]);
        return $q->fetchAll(PDO::FETCH_COLUMN);
    }

}