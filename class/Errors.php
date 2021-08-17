<?php

require_once("resource/errors.php");

class Errors
{
    public function Log($message, $file, $line, $userID = null)
    {
        CreateLog($message, $file, $line, $userID);
    }
}