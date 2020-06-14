<?php
/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 19:15
 */
session_start();


foreach (glob("globals/*.php") as $filename)
{
    include_once($filename);
}

foreach (glob("datasource/*.php") as $filename)
{
    include_once($filename);
}

foreach (glob("dao/*.php") as $filename)
{
    include_once($filename);
}

foreach (glob("auth/*.php") as $filename)
{
    include_once($filename);
}

foreach (glob("helpers/*.php") as $filename)
{
    include_once($filename);
}



$tokenChecker = new CheckAuthToken();

