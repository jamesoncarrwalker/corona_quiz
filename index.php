<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 18:06
 */

include_once("bootstrap.php");

//if we are mid quiz

if($tokenChecker->checkAuthToken()) {
    header('location: ' . Constants::ROOT . 'quiz?quiz_id=' . $_SESSION[Constants::ACTIVE_QUIZ] ?? $_COOKIE[Constants::ACTIVE_QUIZ]);
    die();
}

?>

<!DOCTYPE html>
<html>

<?php include_once("template/head.php")?>

    <body>
    <div class="container-fluid dicks_header edge bottom">
        <div class="row">
            <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-left">
                <h4>Welcome</h4>
            </div>
        </div>

    </div>

        <? if(isset($_SESSION['error'])){?>
        <div class="container warning">
            <p><?echo $_SESSION['error']?></p>
        </div>
        <?}?>

        <div class="container">
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 sign" >

                </div>
            </div>
            <div class="row">
                <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 text-center">
                    <h2>Where every pint comes with the perfect head</h2>
                </div>
            </div>

            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 text-center"><a href="join.php" class="btn btn-info text-center ">Join a quiz</a></div>
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 text-center"><a href="create.php" class="btn btn-info text-center ">Create a quiz</a></div>
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 text-center"><a href="marksheet.php" class="btn btn-info ">Host a quiz</a></div>
            </div>
        </div>
    </body>

   <?include_once("template/toe.php")?>

</html>
