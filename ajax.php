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
        if(isset($data->points) && $data->points == 0) {
            $correct = false;
        } else {
            $correct =  isset($data->correct) ? $data->correct : isset($data->points);
        }

        $half = isset($data->half) ? true : (isset($data->points) && $data->points == 0.5);
        if($correct) {
            $answerDao->markAnswerCorrect($data->quizId,$data->answerId,$data->points??1);
            $response = (object) ['total' => $answerDao->getTeamScoreForQuiz($data->teamId,$data->quizId),'correct' => true,'team' => $data->teamId,'answer' => $data->answerId,'round_total' => $answerDao->getTeamScoreForQuizRound($data->teamId,$data->quizId,$data->round),'half' => $half];
        } else {
            $answerDao->markAnswerIncorrect($data->quizId,$data->answerId);
            $response = (object) ['total' => $answerDao->getTeamScoreForQuiz($data->teamId,$data->quizId),'correct' => false,'team' => $data->teamId,'answer' => $data->answerId,'round_total' => $answerDao->getTeamScoreForQuizRound($data->teamId,$data->quizId,$data->round)];
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


                    if($questionDao->addQuestions($data->quiz,$data->round,$questions,$data->points)) {
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


    } else if ($endpoint == 'setRoundQuestionVisibility') {
        $roundDao = new RoundDataAccessService($pdo);

        $response = ['updated' => $roundDao->setRoundQuestionVisibility($data->quiz,$data->round,$data->show),'round' => $data->round,'show' => $data->show];
        $return = addslashes(json_encode($response));
        echo $return;
    } else {
        echo 'invalid endpoint';
    }

} else {
    echo 'no endpoint';
}