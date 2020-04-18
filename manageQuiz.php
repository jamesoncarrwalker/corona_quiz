<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 20:49
 */
include_once("bootstrap.php");
$rounds = [];
$questions = [];
$canCreateQuiz = false;



if($quizMasterId = $_SESSION['quizMasterId']) {
    $canCreateQuiz = true;
} else {
    header('location: index.php');
    die();
}

if(!isset($_GET['quiz_id'])) {
    header('location: index.php');
    die();
}

if(isset($_POST['addQuestions'])) {
    $questionDao = new QuestionDataAccessService(new PDOConn());
    $questionDao->addQuestions($_GET['quiz_id'],$_POST['round'],$_POST['question']);
    $i = 0;

    header('location: manageQuiz.php?quiz_id=' . $_GET['quiz_id'] );
    die();
}

if($canCreateQuiz) {
    $pdo = new PDOConn();
    $questionDao = new QuestionDataAccessService($pdo);
    $roundDap = new RoundDataAccessService($pdo);
    $rounds = $roundDap->getAllRoundForQuiz($_GET['quiz_id']);
    $questions = $questionDao->getAllQuestionsForQuiz($_GET['quiz_id']);
}



?>

<!DOCTYPE html>
<html>

<?php include_once("template/head.php")?>

<body>
<?php include_once("template/header.php")?>


<div class="container">
    <div class="row">
        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
            <h1>Ok, let's add some questions!</h1>
        </div>
    </div>
    <script type="text/javascript">
        function addQuestion(round) {

            $('.' + round).last().after('<input class="' + round + ' round_question" name="question[]" type="text">');

        }
    </script>
    <div class="row">
    <?foreach($rounds as $round) {?>

        <div class="col-xs-12 col-sm-4 col-md-3 col-lg-3">
            <?echo '<h3>Round: ' . $round->title . '</h3>'?>
            <ol id="round_<?echo $round->UUID?>_questions_list" >
                <?
                    $roundQuestions = array_filter(array_map(function($question) use ($round) {
                        return $question->round == $round->UUID ? $question : null;
                    },$questions ));

                foreach($roundQuestions as $q) {
                    echo '<li>' . $q->title . '</li>';
                }

                ?>
            </ol>

            <form class="form-inline" id="round_<?echo $round->UUID ?>_form">
                <div class="form-group mb-2">
                    <input class="<?echo $round->UUID ?> round_question" name="question[]" type="text">
                    <button class="btn btn-sm btn-success" type="button" onclick="addQuestion('<?echo $round->UUID?>')">Add Question</button>
                    </br>
                    </br>
                    <a name="addQuestions" onclick="saveQuestions('<?echo $_SESSION['quizMasterId']?>','<?echo $_GET['quiz_id'] ?>','<?echo $round->UUID?>')" class="btn btn-primary mb-2">Save Questions</a>
                </div>
            </form>
        </div>
    <?}?>
    </div>
</div>
</body>

<?include_once("template/toe.php")?>

</html>