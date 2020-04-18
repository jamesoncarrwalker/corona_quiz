<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 31/03/2020
 * Time: 21:39
 */
include_once('bootstrap.php');
$endpoint = $_GET['endpoint'] ?? null;

if(isset($endpoint)) {
    $pdo = new PDOConn();
    $data = json_decode($_GET['data']);

    if($endpoint == 'mark_answer') {
        $answerDao = new AnswerDataAccessService($pdo);
        if($data->correct === true) {
            $answerDao->markAnswerCorrect($data->quizId,$data->answerId);
            $response = (object) ['total' => $answerDao->getTeamScoreForQuiz($data->teamId,$data->quizId),'correct' => $data->correct,'team' => $data->teamId,'answer' => $data->answerId,'round_total' => $answerDao->getTeamScoreForQuizRound($data->teamId,$data->quizId,$data->round)];
        } else {
            $answerDao->markAnswerIncorrect($data->quizId,$data->answerId);
            $response = (object) ['total' => $answerDao->getTeamScoreForQuiz($data->teamId,$data->quizId),'correct' => $data->correct,'team' => $data->teamId,'answer' => $data->answerId,'round_total' => $answerDao->getTeamScoreForQuizRound($data->teamId,$data->quizId,$data->round)];
        }

        $return = addslashes(json_encode($response));
        echo $return;
    } else {
        echo 'invalid endpoint';
    }

} else {
    echo 'no endpoint';
}