<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 19:11
 */
?>
<head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css">
        <link rel="stylesheet" href="/css/bootstrap.min.css">
        <link rel="stylesheet" href="css.css">
        <link href='http://fonts.googleapis.com/css?family=Great+Vibes' rel='stylesheet' type='text/css'>
        <?if($_SERVER['HTTP_HOST'] == 'localhost') {?>
                <script type="application/javascript" src="globals_dev.js"></script>
        <?} else {?>
                <script type="application/javascript" src="globals.js"></script>
        <?}?>
        <script type="application/javascript" src="js.js"></script>
        <title>Welcome to the Quiz at The Dick's</title>
    </head>