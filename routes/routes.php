<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 19:21
 */


$page = substr($_SERVER['REQUEST_URI'], strlen(Constants::ROOT) -1);

if(file_exists($page . '.php')) {
    include_once($page . '.php');
} else {
    $_SESSION['error'] = 'Could not find that page';
    include_once('welcome.php');
}