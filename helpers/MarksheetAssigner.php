<?php

/**
 * Created by PhpStorm.
 * User: jamesskywalker
 * Date: 14/06/2020
 * Time: 08:31
 */
class MarksheetAssigner {

    static function assignMarksheets(array $teamIds):array {

        $returnArray = [];

        foreach($teamIds as $key => $id) {
            if($key == count($teamIds) -1) {
                //assign the first el
                $returnArray[$id] = $teamIds[0];
            } else {
                //assign the next el
                $returnArray[$id] = $teamIds[$key + 1];
            }


        }
        return $returnArray;
    }

}