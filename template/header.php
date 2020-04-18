<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 31/03/2020
 * Time: 16:21
 */?>

<div class="container-fluid dicks_header edge bottom ">
    <div class="container">
        <div class="row">
            <div class="col-xs-6 col-sm-9 col-md-9 col-lg-9">
                <ul class="list-inline list-unstyled dicks_nav">
                    <li><a href="index.php">Home</a></li>
                    <li><a href="join.php">Join a quiz</a></li>
                    <?if(isset($_SESSION['quizMasterId'])){?>

                        </li><a href="marksheet.php">Marksheet</a></li>
                    <?}?>
                </ul>
            </div>
            <div class="col-xs-6 col-sm-3 col-md-3 col-lg-3 sign icon pull-right" >


            </div>
        </div>
    </div>


</div>
