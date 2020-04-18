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
$authDao = new AuthTokenDataAccessService(new PDOConn());
if(isset($_POST['auth'])) {

    if ($quizMasterId = $authDao->checkQuizMasterCredentials($_POST)) {
        $canCreateQuiz = true;
        $_SESSION['quizMasterId'] = $quizMasterId;
    } else {
        $_SESSION['error'] = 'Could not confirm your details';
        $_SESSION['quizMasterId'] = null;
    }
}  else if (isset($_SESSION['quizMasterId'])) {
    if ($quizMasterId = $authDao->checkQuizMasterToken($_SESSION['quizMasterId'])) {
        $canCreateQuiz = true;
        $_SESSION['quizMasterId'] = $quizMasterId;
    } else {
        $_SESSION['error'] = 'Could not confirm your details';
        $_SESSION['quizMasterId'] = null;
    }
}

if (isset($_POST['createQuiz']) && $canCreateQuiz) {
    $rounds = array_filter($_POST['round']);

    if (count($rounds) > 0) {
        $pdo = new PDOConn();
        $quizDao = new QuizDataAccessService($pdo);
        if ($quizId = $quizDao->create($_POST)) {
            $roundsDao = new RoundDataAccessService($pdo);
            $roundsDao->addRounds($quizId, $rounds);
            header('location: manageQuiz.php?quiz_id=' . $quizId);
        } else {
            $_SESSION['error'] = 'Could not create quiz';
        }
    } else {
        $_SESSION['error'] = 'You must have at least 1 round';
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
            <h1>Welcome Quiz Master</h1>
            <h2><?echo  $canCreateQuiz ? 'Build your quiz!' : 'Please log in'?></h2>
        </div>
    </div>
    <?if($canCreateQuiz){?>
        <script type="text/javascript">
            function addRound() {

                $('.quiz_round').last().after('<input class="quiz_round" name="round[]" type="text" placeholder="e.g. Sport,TV & Movies">');

            }
        </script>
        <div class="row">
            <form class="form-inline" action="create.php" method="post">
                <div class="form-group mb-2">
                    <input name="date" type="hidden" value="1" class="form-control-plaintext" id="date" placeholder="123ABC">
                </div>
                <div class="form-group mb-2">
                    <label for="title" >Quiz name</label>
                    </br>
                    <input name="title" type="text" class="form-control-plaintext" id="title" placeholder="witty name">
                </div>

                </br>
                </br>

                <div class="form-group mb-2">
                    <label for="round" >Rounds</label>
                    </br>
                    <input class="quiz_round" name="round[]" type="text" placeholder="e.g. Sport,TV & Movies">
                     <button class="btn btn-success" type="button" onclick="addRound()">Add Round</button>
                </div>
                </br>
                </br>
                <div class="form-group mb-2">
                    <input type="hidden" value="<?echo $quizMasterId?>" name="host">
                    <button name="createQuiz" type="submit" class="btn btn-primary mb-2">Create Quiz</button>
                </div>
            </form>
        </div>


    <? } else {?>

    <div class="row text-center">
        <form class="form-inline" action="create.php" method="post">
            <div class="form-group mb-2">
                <label for="username">Username</label>
                <input name="username" type="text" class="form-control-plaintext" id="username" placeholder="username">
            </div>
            <div class="form-group mb-2">
                <label for="password" >Password</label>
                <input name="password" type="text" class="form-control-plaintext" id="password" placeholder="password">
            </div>
            <button name="auth" type="submit" class="btn btn-primary mb-2">Make a Quiz</button>
        </form>
    </div>
    <?}?>
</div>
</body>

<?include_once("template/toe.php")?>

</html>