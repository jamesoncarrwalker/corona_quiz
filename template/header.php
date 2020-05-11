<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 31/03/2020
 * Time: 16:21
 */?>

<div class="container-fluid dicks_header">
    <div class="container">
        <div class="row">
            <div class="col-xs-6 col-sm-9 col-md-9 col-lg-9">
                <ul class="list-inline list-unstyled dicks_nav">
                    <li class="text-center"><a href="index.php">Home</a></li>
                    <?if(isset($_SESSION['quizMasterId'])){?>
                        <li class="text-center"><a href="create.php">Create a quiz</a></li>
                        <li class="text-center"><a href="marksheet.php">Host a quiz</a></li>
                    <?}?>
                    <li class="text-center"><a href="join.php">Join a quiz</a></li>
                </ul>
            </div>
            <div class="col-xs-3 col-xs-offset-3 col-sm-1 col-sm-offset-2 col-md-offset-1 col-md-2 col-lg-offset-1 col-lg-2 icon pull-right" >
                <a href="index.php"><img src="shield.png" alt="The Dicks Inn" class="img-responsive pull-right"></a>

            </div>
        </div>
    </div>

</div>

<? if(isset($_SESSION['error'])){?>
    <div class="container alert alert-danger text-center">
        <p><?echo $_SESSION['error']?></p>
    </div>
<?}?>
