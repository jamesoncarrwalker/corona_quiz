<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:09
 */
include_once("bootstrap.php");

if(isset($_GET['clear'])) {
    unset($_SESSION['quiz_id'],$_SESSION['team_id']);
    unset($_COOKIE['quiz_id'],$_COOKIE['team_id']);
// empty value and expiration one hour before
   setcookie('quiz_id', '', time() - 3600);
   setcookie('team_id', '', time() - 3600);
}

if(
    (isset($_SESSION['quiz_id']) || isset($_COOKIE['quiz_id']))
    && (isset($_SESSION['team_id']) || isset($_COOKIE['team_id']))
) {

    header('location: quiz.php?quiz_id=' . ($_SESSION['quiz_id']));
    die();
}



if(isset($_POST['joinQuiz'])) {
    $pdo = new PDOConn();
    $quizDao = new QuizDataAccessService($pdo);
    $quiz = $quizDao->getQuizFromCode($_POST['quizCode']);
    if(isset($quiz->UUID)) {

        $teamDao = new TeamDataAccessService($pdo);
        $team = $teamDao->getTeamByName($_POST['teamName'],$quiz->UUID);
        if(!isset($team->UUID)) {
            $teamDao->create($_POST['teamName'],$quiz->UUID);
            $team = $teamDao->getTeamByName($_POST['teamName'], $quiz->UUID);
        }
        $_SESSION['team_id'] = $team->UUID;
        setcookie('team_id',$team->UUID,time() + (60*60*3));

        $_SESSION['quiz_id'] = $quiz->UUID;
        setcookie('quiz_id',$quiz->UUID,time() + (60*60*3));

        header('location: quiz.php?quiz_id=' . $quiz->UUID);
        die();
    } else {
        $_SESSION['error'] = 'Could not find that quiz';
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
            <h1>Feeling Quiz-Tastic?</h1>
            <h2>Enter the code your host gave you into the box below and pick a team name, then get ready to quiz your night away</h2>
        </div>
    </div>

    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 sign" style="min-height: 250px;">

        </div>
    </div>

    <div class="row">
        <form class="form-inline" action="join.php" method="post">
            <div class="form-group mb-2">
                <label for="join" class="sr-only">Quiz Code</label>
                <input name="quizCode" type="text" class="form-control-plaintext" id="join" placeholder="123ABC">
            </div>
            <div class="form-group mb-2">
                <label for="join" class="sr-only">Team name</label>
                <input name="teamName" type="text" class="form-control-plaintext" id="join" placeholder="witty Team name">
            </div>
            <button name="joinQuiz" type="submit" class="btn btn-primary mb-2">Join Quiz</button>
        </form>
    </div>
</div>
</body>

<?include_once("template/toe.php")?>

</html>