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

    if($endpoint == 'markAnswer') {
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
    } else if ($endpoint == 'addRoundQuestions') {
        $quizDao = new QuizDataAccessService($pdo);
        $quiz = $quizDao->getQuizById($data->quiz ?? '');

        if(isset($quiz->UUID)) {
            if($quiz->host == $data->host) {

                $questions = array_filter($data->question);
                if(count($questions) > 0) {
                    $questionDao = new QuestionDataAccessService($pdo);


                    if($questionDao->addQuestions($data->quiz,$data->round,$questions)) {
                        $response = ['round' => $data->round,'questions' => $questionDao->getQuestionsForRound($quiz->UUID,$data->round)];
                        $return = addslashes(json_encode($response));
                        echo $return;
                    } else {
                        echo 'could not add questions';
                    }
                } else {
                    echo 'no questions';
                }




            } else {
                echo 'not your quiz';
            }
        } else {
            echo 'Couldn\'t find that quiz';
        }


    } else {
        echo 'invalid endpoint';
    }

} else {
    echo 'no endpoint';
}