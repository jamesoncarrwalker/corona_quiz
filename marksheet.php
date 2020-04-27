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
$round = null;
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

    $quizzes = $quizDao->getUserCreatedQuizzes($quizMasterId);
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
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 ">
            <h2 class="text-center"><?echo $quizSelected ? $quiz->title : 'Welcome Quiz Master' ?></h2>
        </div>
    </div>
    <?if($canMarkQuiz){?>
        <div class="row">
        <?if(!$quizSelected){?>
            <ul class="list-unstyled list-inline panel_list">
                <?foreach ($quizzes as $quiz) {?>
                    <li><a href="marksheet.php?quiz_id=<?echo $quiz->UUID?>"><?echo $quiz->title?></a>  (<?echo $quiz->invitation_code ?>)</li>
                <?}?>
            </ul>

        <?} else {?>
            <div class="col-xs-12 col-sm-3 col-md-2 col-lg-2 ">
                <ul class="list-unstyled">
                    <li class="section_heading">Invitation code</li>
                    <li class="panel"><?echo $quiz->invitation_code?></li>
                    <li class="section_heading">Rounds: </li>
                    <?foreach($rounds as $r){?>
                        <li class=" panel <? if(isset($round) && $round->UUID == $r->UUID) echo 'active' ?>"><a href="marksheet.php?quiz_id=<?echo $quiz->UUID?>&round=<?echo $r->UUID?>"><?echo $r->title?></a>
                        </li>
                        <li> Show questions to teams?
                            <span id="<?echo $r->UUID?>_questions_visible" class="glyphicon glyphicon-ok marksheet award_glyph <?echo ($r->show_answers > 0 ? 'correct' : '')?> " onclick="updateShowRoundQuestions('<?echo $quiz->UUID ?>','<?echo $r->UUID?>', true)"></span>

                            <span id="<?echo $r->UUID?>_questions_hidden" class="glyphicon glyphicon-remove marksheet award_glyph <? echo ($r->show_answers < 1 ? 'incorrect' : '')?>" onclick="updateShowRoundQuestions('<?echo $quiz->UUID?>','<?echo $r->UUID?>', false)"></span>
                        </li>
                    <?}?>
                </ul>
            </div>
            <div class="col-xs-12 col-sm-9 col-md-10 col-lg-10 edge left">
                <h4 class="section_heading">Scores</h4>
                <ul class="list-inline list-unstyled panel_list">
                    <?if(count($teams) > 0) {
                        foreach ($teams as $team) {
                            ?>
                            <li class="panel">
                                <span class="section_heading"><? echo $team->team_name ?></span></br>
                                Quiz Total: <span id="<? echo $team->UUID . '_total_score' ?>">
                                    <? echo abs($answerDao->getTeamScoreForQuiz($team->UUID, $quiz->UUID)) ?>
                                </span>

                                <? if ($roundSelected) { ?>
                                    </br>
                                    This round: <span
                                        id="<? echo $team->UUID . '_round_total_score' ?>"><? echo $answerDao->getTeamScoreForQuizRound($team->UUID, $quiz->UUID, $round->UUID) ?? '-' ?></span>
                                <?
                                } ?>
                            </li>
                        <?
                        }
                    } else {?>
                        <li class="panel"><span class="section_heading">No teams have joined yet</span></li>
                    <?}?>
                </ul>

                <?if($roundSelected) {?>
                    <ol class="list-unstyled">

                        <?

                        $teamAnswers = $answerDao->getQuestionsWithAnswersForRound($quiz->UUID,$round->UUID);

                        foreach($teamAnswers as $questionId => $answers) {
                            $points = $answers[0]->points_available ?? 1;
                            ?>
                            <li class="col-xs-12 col-sm-12 col-md-12 panel"><?  echo ($answers[0]->question ?? '....')?>? (<?echo $points?> points)</li>
                            <li class="col-xs-12 col-sm-12 col-md-12">
                                <ul class="list-unstyled list-inline  ">
                                    <?foreach($answers as $teamAnswer) {
                                        if(!isset($teamAnswer->team_UUID))continue;
                                        ?>
                                        <li class="col-xs-6 col-sm-4 col-md-3 col-lg-3 ">
                                            <p class="section_heading"><?echo $teams[$teamAnswer->team_UUID]->team_name ?? 'Someone'?></p>
                                            <p><?echo $teamAnswer->answer ?? ''?></p>
                                        <?if($points == 1) {?>
                                            <span id="<?echo $teamAnswer->UUID?>_correct" class="glyphicon glyphicon-ok marksheet award_glyph <?echo ($teamAnswer->points > 0.5 ? 'correct' : '')?> " onclick="markAnswer('<?echo $quiz->UUID ?>','<?echo $teamAnswer->team_UUID?>', '<?echo $teamAnswer->UUID ?>','<?echo $round->UUID?>', true)"></span>
                                            <span id="<?echo $teamAnswer->UUID?>_half" data-id="<?echo $teamAnswer->UUID?>" data-quiz="<?echo $quiz->UUID ?>"  data-team="<?echo $teamAnswer->team_UUID?>" data-round="<?echo $round->UUID?>" onclick="markAnswerWithHalf(this)" class=" marksheet award_glyph <?echo ($teamAnswer->points == 0.5 ? 'half-point' : '')?> ">1/2</span>
                                        <?} else {?>
                                            <select id="<?echo $teamAnswer->UUID?>_select" data-id="<?echo $teamAnswer->UUID?>" data-quiz="<?echo $quiz->UUID ?>"  data-team="<?echo $teamAnswer->team_UUID?>" data-round="<?echo $round->UUID?>" class="<?echo ($teamAnswer->points == 0.5 ? 'half-point' : ($teamAnswer->points > 0.5 ? 'correct' : ''))?>" onchange="markAnswerWithPoints(this)">
                                                <?for($i = 0; $i <= $points; $i += 0.5) {?>
                                                    <option <?if($i == $teamAnswer->points) {echo "selected=\"true\"";} ?> value="<?echo $i?>"><?echo $i?></option>
                                                <?}?>
                                            </select>
                                            <?}?>
                                            <span id="<?echo $teamAnswer->UUID?>_incorrect" class="glyphicon glyphicon-remove marksheet award_glyph <? echo ($teamAnswer->points == -1 ? 'incorrect' : '')?>" onclick="markAnswer('<?echo $quiz->UUID?>','<?echo $teamAnswer->team_UUID?>', '<?echo $teamAnswer->UUID?>','<?echo $round->UUID?>', false)"></span>
                                        </li>
                                    <?}?>

                                </ul>
                            </li>

                        <?}?>
                    </ol>
                <?}?>
            </div>

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
                    <label for="username" >Username</label>
                    <input name="username" type="text" class="form-control-plaintext" id="username" placeholder="username">
                </div>
                <div class="form-group mb-2">
                    <label for="password" >Password</label>
                    <input name="password" type="password" class="form-control-plaintext" id="password" placeholder="password">
                </div>
                <button name="auth" type="submit" class="btn btn-primary mb-2">Mark Quiz</button>
            </form>
        </div>
    <?}?>
</div>
</body>

<?include_once("template/toe.php")?>

</html>