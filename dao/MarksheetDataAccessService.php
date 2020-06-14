<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 14/06/2020
 * Time: 08:13
 */
class MarksheetDataAccessService {

    private $conn;

    public function __construct(PDOConn $PDOConn) {
        $this->conn = $PDOConn->connect();
    }

    public function setMarksheetForRound(string $quizId, string $roundId, array $teams) {

        $pdoBindArray = [];
        $sql = [];
        $i = 0;
        foreach ($teams as $toMark => $markedBy) {
            $sql[] = "(
                        (SELECT uuid()),
                        :quiz_" . $i . " ,
                        :round_" . $i . " ,
                        :toMark_" . $i . " ,
                        :markedBy_" . $i . "

                    )";
            $pdoBindArray[':quiz_' . $i] = $quizId;
            $pdoBindArray[':round_' . $i] = $roundId;
            $pdoBindArray[':toMark_' . $i] = $toMark;
            $pdoBindArray[':markedBy_' . $i] = $markedBy;


        }
        $sql = implode(',', $sql);
        $q = $this->conn->prepare("INSERT INTO assigned_marksheet (UUID, quiz_UUID, round_UUID, team_to_mark, round_marked_by) VALUES " . $sql);

        return $q->execute($pdoBindArray);

    }

    public function clearMarksheetAssignmentForRound(string $quizId, string $roundId) {
        $q = $this->conn->prepare("DELETE FROM assigned_marksheet WHERE quiz_UUID = :quiz AND round_UUID = :round");
        $q->bindParam(':quiz',$quizId,PDO::PARAM_STR);
        $q->bindParam(':round',$roundId,PDO::PARAM_STR);

        return $q->execute();
    }

    public function getRoundsWithAssignedMarksheets(string $quizId) {
        $q = $this->conn->prepare("SELECT DISTINCT(round_UUID)
                                   FROM assigned_marksheet
                                   WHERE quiz_UUID = :quiz");
        $q->bindParam(':quiz',$quizId,PDO::PARAM_STR);
        $q->execute();
        return $q->fetchAll(PDO::FETCH_COLUMN);
    }
}

