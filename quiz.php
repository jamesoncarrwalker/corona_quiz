<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:06
 */
include_once("bootstrap.php");
$pdo = new PDOConn();
$quizDao = new QuizDataAccessService($pdo);
$teamDao = new TeamDataAccessService($pdo);
$roundDao = new RoundDataAccessService($pdo);
$questionDao = new QuestionDataAccessService($pdo);
$answerDao = new AnswerDataAccessService($pdo);
$marksheetDao = new MarksheetDataAccessService($pdo);
$quiz = [];
$list = true;

if(!isset($_GET['quiz_id']) || !(isset($_SESSION['team_id']) || isset($_COOKIE['team_id']))) {
    $_SESSION['error'] = 'You must specify a quiz';
    header('location: join.php');
    die();
}

$team = $teamDao->getTeamById($_SESSION['team_id'] ?? $_COOKIE['team_id'],$_GET['quiz_id']);
$quiz = $quizDao->getQuizById($_GET['quiz_id']);
$rounds = $roundDao->getAllRoundForQuiz($quiz->UUID);
$pointsPerRound = $teamDao->getTeamPointsForQuizByRound($quiz->UUID,$team->UUID);

if(isset($_GET['round'])) {
    foreach($rounds as $r) {
        if($r->UUID == $_GET['round']) {
            $round = $r;
            break;
        }
    }
} else {
    $round = $rounds[0];
}

$answers = $answerDao->getTeamAnswersForRound($team->UUID,$quiz->UUID,$round->UUID);
$canAnswer = count($answers) === 0;

if(isset($_POST['answers']) && $canAnswer ) {
    $responses = array_combine($_POST['question'],$_POST['answer']);

    foreach($responses as $key => $response) {
        $answerDao->addTeamAnswer($team->UUID,$quiz->UUID,$key,$response);
    }
    header('location: quiz.php?quiz_id=' . $quiz->UUID . '&round=' . $round->UUID);
}

$questions = $questionDao->getQuestionsForRound($quiz->UUID,$round->UUID);

?>

<!DOCTYPE html>
<html>

<?php include_once("template/head.php")?>

<body>
<?php include_once("template/header.php")?>


<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
            <h1><?echo $quiz->title?></h1>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-3 col-md-3 col-lg-3" style="min-height: 250px;">
            <h4><?echo $team->team_name?></h4>
            <h4 class="section_heading">Rounds</h4>
            <ul class="list-unstyled panel_list">
               <?foreach($rounds as $r){?>
                   <a href="quiz.php?quiz_id=<?echo $quiz->UUID?>&round=<?echo $r->UUID?>"><li class="panel <?if($r->UUID == $round->UUID) echo "active";?>">
                            <?echo $r->title?>
                         ( score: <span id="<?echo $r->UUID?>_total_score"><?echo $pointsPerRound[$r->UUID]?? '/'?></span> )</li></a>
                <?}?>
                <li>Total : <span id="team_total_score"><? echo abs($answerDao->getTeamScoreForQuiz($team->UUID,$quiz->UUID))?></span></li>
            </ul>
        </div>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 edge left ">
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 text-center toggle_panel_control active" onclick="showPanel('user_answers')"><h4>Your answers</h4></div>
            <div class="col-xs-6 col-sm-6 col-md-6 col-lg-6 text-center toggle_panel_control"  onclick="showPanel('user_marksheet')"><h4>Mark another team</h4></div>
        </div>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 edge left user_answers toggle_panel">
            <?php if($canAnswer){?>
            <form action="" method="post">
                <?$i = 1;?>
                <?foreach($questions as $question){?>
                    <div class="form-group mb-2 ">
                        <label for="<?echo $question->UUID?>" class="">Question <?echo $i . ($round->show_answers ? ' - ' . $question->title : "" )?> <span class="available_points_for_question">available:<?echo $question->points?></span></label>
                        <?$i++;?>
                        </br>
                        <input type="hidden" name="question[]" value="<?echo $question->UUID?>">
                        <textarea name="answer[]" type="text" class="form-control-plaintext col-xs-12 col-sm-12 col-md-12 col-lg-12" id="<?echo $question->UUID?>" placeholder="123ABC"></textarea>
                    </div>
                <?}?>
                <input type="hidden" name="round" value="<?echo $round->UUID?>">
                <input type="hidden" name="quiz" value="<?echo $quiz->UUID?>">
                <input type="hidden" name="team" value="<?echo $team->UUID?>">
                <button name="answers" type="submit" class="btn btn-primary mb-2 submit_button">Submit answers</button>
            </form>
            <?} else {?>
                <ol >
                    <?$i = 0;?>
                    <?foreach($answers as $answer){?>
                        <li><strong><?echo $questions[$i]->title ?> ?  </strong><?echo $answer->answer?>
                            <span id="<?echo $answer->UUID?>_correct" class="glyphicon glyphicon-ok award_glyph <? echo ($answer->points > 0 ? 'correct' : '')?> " ></span>
                            <span id="<?echo $answer->UUID ?>_incorrect" class="glyphicon glyphicon-remove award_glyph <? echo ($answer->points < 0 ? 'incorrect' : '') ?>" ></span>
                            <span class="available_points_for_question"><span id="<?echo $answer->UUID ?>_points_awarded"><?echo ($answer->points > 0 ? $answer->points : 0)?></span> / <?echo $questions[$i]->points?></span>
                        </li>
                    <?$i++;}?>
                </ol>

            <?}?>
        </div>

        <div class="col-xs-12 col-sm-9 col-md-9 col-lg-9 edge left user_marksheet toggle_panel hidden">
            <p id="marksheetMessage">you can mark another teams answers here if your quiz master has set this up</p>
            <ol id="marksheetAnswers"></ol>

        </div>

    </div>
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <a href="join.php?clear=true" class="btn btn-primary mb-2 submit_button">Leave quiz</a>
    </div>

</div>
</body>

<?include_once("template/toe.php")?>
<script>
    $(function() {
        listenForAnswerSheet("<?echo $quiz->UUID?>","<?echo $round->UUID?>","<?echo $team->UUID?>");
        getTeamScoresUpdate("<?echo $quiz->UUID?>","<?echo $round->UUID?>","<?echo $team->UUID?>");
        getMarkedAnswers("<?echo $quiz->UUID?>","<?echo $round->UUID?>");
    });
</script>

</html>