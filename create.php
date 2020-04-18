<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:12
 */
include_once("bootstrap.php");

$canCreateQuiz = false;
$quizMasterId = null;
if(isset($_POST['auth'])) {
    $authDao = new AuthTokenDataAccessService(new PDOConn());
    if ($quizMasterId = $authDao->checkQuizMasterCredentials($_POST)) {
        $canCreateQuiz = true;
        $_SESSION['quizMasterId'] = $quizMasterId;
    } else {
        $_SESSION['error'] = 'Could not confirm your details';
    }
} else if (isset($_POST['createQuiz'])) {
    $pdo = new PDOConn();
    $quizDao = new QuizDataAccessService($pdo);
    if($quizId = $quizDao->create($_POST)) {
        $roundsDao = new RoundDataAccessService($pdo);
        $roundsDao->addRounds($quizId,$_POST['round']);
        header('location: manageQuiz.php?quiz_id=' . $quizId);
    } else {
        $_SESSION['error'] = 'Could not create quiz';
    }

}


?>
<!DOCTYPE html>
<html>

<?php include_once("template/head.php")?>

<body>
<?php include_once("template/header.php")?>
<? if(isset($_SESSION['error'])){?>
    <div class="container warning">
        <p><?$_SESSION['error']?></p>
    </div>
<?}?>

<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
            <h1>Welcome Quiz Master</h1>
            <h2><?echo  $canCreateQuiz ? 'Build your quiz!' : 'Please log in'?></h2>
        </div>
    </div>
    <?if($canCreateQuiz){?>
        <script type="text/javascript">
            function addRound() {

                $('.quiz_round').last().after('<input class="quiz_round" name="round[]" type="text">');

            }
        </script>
        <div class="row">
            <form class="form-inline" action="create.php" method="post">
                <div class="form-group mb-2">
                    <label for="date" class="sr-only">date</label>
                    <input name="date" type="number" class="form-control-plaintext" id="date" placeholder="123ABC">
                </div>
                <div class="form-group mb-2">
                    <label for="title" class="sr-only">Quiz name</label>
                    <input name="title" type="text" class="form-control-plaintext" id="title" placeholder="witty name">
                </div>
                <input class="quiz_round" name="round[]" type="text">
                <button type="button" onclick="addRound()">Add Round</button>
                <input type="hidden" value="<?echo $quizMasterId?>" name="host">
                <button name="createQuiz" type="submit" class="btn btn-primary mb-2">Create Quiz</button>
            </form>
        </div>


    <? } else {?>

    <div class="row">
        <form class="form-inline" action="create.php" method="post">
            <div class="form-group mb-2">
                <label for="username" class="sr-only">Username</label>
                <input name="username" type="text" class="form-control-plaintext" id="username" placeholder="username">
            </div>
            <div class="form-group mb-2">
                <label for="password" class="sr-only">password</label>
                <input name="password" type="text" class="form-control-plaintext" id="password" placeholder="password">
            </div>
            <button name="auth" type="submit" class="btn btn-primary mb-2">Join Quiz</button>
        </form>
    </div>
    <?}?>
</div>
</body>

<?include_once("template/toe.php")?>

</html>