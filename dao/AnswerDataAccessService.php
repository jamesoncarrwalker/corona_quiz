<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:10
 */
class AnswerDataAccessService {

    private $conn;

    public function __construct(PDOConn $conn) {
        $this->conn = $conn->connect();
    }

    public function getTeamAnswersForRound(string $team, string $quiz, string $round) {
        $q = $this->conn->prepare("SELECT answer.*, question.title, question.points AS points_available,question.round
                                    FROM answer
                                    INNER JOIN question
                                    ON (question.quiz_UUID = answer.quiz_UUID
                                        AND answer.question_UUID = question.UUID

                                        )
                                    WHERE answer.quiz_UUID = :quiz
                                    AND answer.team_UUID = :team
                                    AND question.round = :round");
        $q->bindParam(':quiz',$quiz,PDO::PARAM_STR);
        $q->bindParam(':team',$team,PDO::PARAM_STR);
        $q->bindParam(':round',$round,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll();
    }

    public function getQuestionsWithAnswersForRound(string $quiz,string $round) {
        $q = $this->conn->prepare(" SELECT question.UUID,question.title AS question,question.points AS points_available, answer.*
                                    FROM question
                                    LEFT JOIN answer ON (
                                        answer.quiz_UUID = question.quiz_UUID
                                        AND answer.question_UUID = question.UUID
                                        )

                                    WHERE question.quiz_UUID = :quiz
                                    AND question.round = :round");
        $q->bindParam(':quiz',$quiz,PDO::PARAM_STR);
        $q->bindParam(':round',$round,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_GROUP);
    }

    public function addTeamAnswers(string $team, string $quiz, array $answers) {
        $sql = "INSERT INTO answer (UUID, quiz_UUID, team_UUID, question_UUID, answer) VALUES ";
        $bindArray = [];
        $values = [];

        foreach($answers as $key => $answer) {
            if(!isset($key) || !isset($answer)) continue;
            $values[] = '((SELECT uuid()),
                            :quiz_' . $key . ',
                            :team_' . $key . ',
                            :question_' . $key . ',
                            :answer_' . $key . ')';

            $bindArray[':answer_' . $key] = $answer;
            $bindArray[':question_' . $key] = $key;
            $bindArray[':quiz_' . $key] = $quiz;
            $bindArray[':team_' . $key] = $team;
        }

        $sql = $sql . implode(',',$values);

        $q = $this->conn->prepare($sql);
        return $q->execute($bindArray);
    }

    public function addTeamAnswer(string $team,string $quiz,string $question,string $answer) {
        $q = $this->conn->prepare("INSERT INTO answer (UUID, quiz_UUID, team_UUID, question_UUID, answer,points) VALUES ((SELECT uuid()),:quiz,:team,:question,:answer,0)");
        return $q->execute([':quiz' => $quiz,':team' => $team, ':question' => $question, ':answer' => $answer]);
    }

    public function markAnswerCorrect(string $quiz, string $answer,float $points = 1) {
        $q = $this->conn->prepare("UPDATE answer
                                   INNER JOIN question ON (question.quiz_UUID = answer.quiz_UUID)
                                   SET answer.points = :points
                                   WHERE answer.UUID = :answer
                                   AND answer.quiz_UUID = :quiz");
        return $q->execute([':answer' => $answer,':quiz' => $quiz,':points' => $points]);
    }

    public function markAnswerIncorrect(string $quiz, string $answer) {
        $q = $this->conn->prepare("UPDATE answer
                                   SET answer.points = -1
                                   WHERE answer.UUID = :answer
                                   AND answer.quiz_UUID = :quiz");
        return $q->execute([':answer' => $answer,':quiz' => $quiz]);
    }

    public function getTeamScoreForQuiz(string $teamId, string $quizId) {
        $q = $this->conn->prepare(" SELECT SUM(IF(points > 0, points, 0)) AS total
                                    FROM answer
                                    WHERE quiz_UUID = :quiz
                                    AND team_UUID = :team");
        $q->execute([':quiz' => $quizId,':team' => $teamId]);
        return $q->fetchColumn();

    }

    public function getTeamScoreForQuizRound(string $teamId, string $quizId,string $roundId) {
        $q = $this->conn->prepare(" SELECT SUM(IF(answer.points > 0, answer.points, 0)) AS total
                                    FROM answer
                                    INNER JOIN question ON (
                                      question.UUID = answer.question_UUID
                                      AND question.quiz_UUID = answer.quiz_UUID

                                    )
                                    WHERE answer.quiz_UUID = :quiz
                                    AND question.quiz_UUID = :quiz
                                    AND question.round = :round
                                    AND answer.team_UUID = :team");
        $q->execute([':quiz' => $quizId,':team' => $teamId,':round' => $roundId]);
        return $q->fetchColumn();

    }

    public function getScoresOverviewForRound(string $quizId,string $roundId) {
        $q = $this->conn->prepare("SELECT answer.team_UUID as team,
                                   SUM(IF(question.round = :round AND answer.points > 0,answer.points,0) ) as roundTotal,
                                   SUM(IF(answer.points > 0,answer.points,0)) as quizTotal
                                   FROM answer
                                   INNER JOIN question ON (question.quiz_UUID = answer.quiz_UUID AND question.UUID = answer.question_UUID)
                                   WHERE answer.quiz_UUID = :quiz
                                   GROUP BY answer.team_UUID");
        $q->bindParam(':round',$roundId,PDO::PARAM_STR);
        $q->bindParam(':quiz',$quizId,PDO::PARAM_STR);
        if(isset($team)) $q->bindParam(':team',$team,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll();

    }

    public function getScoresOverviewForRoundForTeam(string $quizId,string $team = null) {
        $q = $this->conn->prepare("SELECT question.round, SUM(IF(answer.points > 0,answer.points,0) ) as roundTotal
                                   FROM answer
                                   INNER JOIN question ON (question.quiz_UUID = answer.quiz_UUID AND question.UUID = answer.question_UUID)
                                   WHERE answer.quiz_UUID = :quiz
                                   AND answer.team_UUID = :team
                                   GROUP BY question.round");
        $q->bindParam(':quiz',$quizId,PDO::PARAM_STR);
        $q->bindParam(':team',$team,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_KEY_PAIR);

    }



}