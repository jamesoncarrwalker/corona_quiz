<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 30/03/2020
 * Time: 19:28
 */
class CheckAuthToken {

    public function checkAuthToken() {

        if(isset($_SESSION[Constants::ACTIVE_QUIZ]) && $_SESSION[Constants::ACTIVE_QUIZ] != '') return true;
        $dao = new AuthTokenDataAccessService(new PDOConn());

        $token = $_SESSION[Constants::ACTIVE_QUIZ] ?? null;

        if($token == null || $token == '') return false;

        return $dao->checkActiveQuizToken($token);
    }



}