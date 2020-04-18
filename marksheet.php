<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:12
 */
include_once("bootstrap.php");

$canMarkQuiz = false;
$quizSelected = false;
$quizMasterId = null;
$pdo = new PDOConn();
$teamScores = [];

if(isset($_POST['auth'])) {
    $authDao = new AuthTokenDataAccessService($pdo);
    if ($quizMasterId = $authDao->checkQuizMasterCredentials($_POST)) {
        $canMarkQuiz = true;
        $_SESSION['quizMasterId'] = $quizMasterId;
    } else {
        $_SESSION['error'] = 'Could not confirm your details';
    }
} else if (isset($_SESSION['quizMasterId'])) {
    $authDao = new AuthTokenDataAccessService($pdo);
    if ($quizMasterId = $authDao->checkQuizMasterToken($_SESSION['quizMasterId'])) {
        $canMarkQuiz = true;
        $_SESSION['quizMasterId'] = $quizMasterId;
    } else {
        $_SESSION['error'] = 'Could not confirm your details';
    }
}
$quizDao = new QuizDataAccessService($pdo);
$teamDao = new TeamDataAccessService($pdo);
$answerDao = new AnswerDataAccessService($pdo);

if($canMarkQuiz && !isset($_GET['quiz_id'])) {

    $quizes = $quizDao->getQuizzes();
    $quizSelected = false;
} else if($canMarkQuiz) {
    $quiz = $quizDao->getQuizById($_GET['quiz_id']);
    $quizSelected = true;
    $roundSelected = false;
    $teams = $teamDao->getTeamsForQuiz($quiz->UUID);
    $roundsDao = new RoundDataAccessService($pdo);
    $answerDao = new AnswerDataAccessService($pdo);
    $rounds = $roundsDao->getAllRoundForQuiz($quiz->UUID);

    if(isset($_GET['round'])) {
        $round = $roundsDao->getRoundById($_GET['round'], $quiz->UUID);
        $roundSelected = true;
    }


}


?>
<!DOCTYPE html>
<html>

<?php include_once("template/head.php")?>

<body>
<?php include_once("template/header.php")?>


<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
            <h1><?echo $quizSelected ? $quiz->title : 'Welcome Quiz Master' ?></h1>
            <h2><?echo  $canMarkQuiz ? ($quizSelected ? 'Get marking' : 'Choose a quiz') : 'Please log in'?></h2>
        </div>
    </div>
    <?if($canMarkQuiz){?>
        <div class="row">
        <?if(!$quizSelected){?>
            <ul class="list-unstyled list-inline panel_list">
                <?foreach ($quizes as $quiz) {?>
                    <li><a href="marksheet.php?quiz_id=<?echo $quiz->UUID?>"><?echo $quiz->title?></a>  (<?echo $quiz->invitation_code ?>)</li>
                <?}?>
            </ul>

        <?} else {?>
            <h3>Totals</h3>
            <ul class="list-inline list-unstyled panel_list">
                <?foreach($teams as $team){?>
                    <li class="panel"><?echo $team->team_name?> :  <span id="<?echo $team->UUID . '_total_score'?>"><?

                       echo abs($answerDao->getTeamScoreForQuiz($team->UUID,$quiz->UUID)) ?>
                            </span>
                    </li>
                <?}?>
            </ul>

                    <h3 class="section_heading">Select a round to mark</h3>
                    <ul class="list-inline list-unstyled panel_list">
                        <?foreach($rounds as $r){?>
                            <li><a href="marksheet.php?quiz_id=<?echo $quiz->UUID?>&round=<?echo $r->UUID?>"><?echo $r->title?></a></li>
                        <?}?>
                    </ul>
                <?if($roundSelected) {?>
                    <h3 class="section_heading"><?echo $round->title?></h3>

                    <ul class="list-unstyled list-inline">
                        <?foreach($teams as $team){?>
                            <li><h5 class="panel"><?echo  $team->team_name?></h5>
                                <ol class="answer_list">
                                    <?
                                    $teamAnswers = $answerDao->getTeamAnswersForRound($team->UUID,$quiz->UUID,$round->UUID);
                                    foreach($teamAnswers as $answer) {
                                        echo '<li>
                                                    <ul class="list-unstyled">
                                                    <li class="section_heading">' . $answer->title . '?</li>
                                                        <li>' . $answer->answer . '
                                                        <span id="' . $answer->UUID . '_correct" class="glyphicon glyphicon-ok marksheet award_glyph ' . ($answer->points > 0 ? 'correct' : '') .' " onclick="markAnswer(\'' . $quiz->UUID . '\',\'' . $team->UUID . '\', \'' . $answer->UUID . '\',\'' . $round->UUID . '\', true)"></span>
                                                        <span id="' . $answer->UUID . '_incorrect" class="glyphicon glyphicon-remove marksheet award_glyph ' . ($answer->points == -1 ? 'incorrect' : '') .'" onclick="markAnswer(\'' . $quiz->UUID . '\',\'' . $team->UUID . '\', \'' . $answer->UUID . '\',\'' . $round->UUID . '\'  , false)"></span></li>
                                                    </ul>
                                                </li>';
                                    }

                                    ?>
                                </ol>
                             <h5 class="panel">Score: <span id="<?echo $team->UUID . '_round_total_score'?>"><?echo $answerDao->getTeamScoreForQuizRound($team->UUID,$quiz->UUID,$round->UUID)?></span></h5>
                            </li>
                        <?}?>
                    </ul>
                <?}?>
        <?}?>
        </div>



    <? } else {?>
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 sign" style="min-height: 250px;">

            </div>
        </div>

        <div class="row">
            <form class="form-inline" action="marksheet.php" method="post">
                <div class="form-group mb-2">
                    <label for="username" class="sr-only">Username</label>
                    <input name="username" type="text" class="form-control-plaintext" id="username" placeholder="username">
                </div>
                <div class="form-group mb-2">
                    <label for="password" class="sr-only">password</label>
                    <input name="password" type="text" class="form-control-plaintext" id="password" placeholder="password">
                </div>
                <button name="auth" type="submit" class="btn btn-primary mb-2">Mark Quiz</button>
            </form>
        </div>
    <?}?>
</div>
</body>

<?include_once("template/toe.php")?>

</html>