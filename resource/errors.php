<?php
require_once("handler/database.php");

function CreateLog($message, $file, $line, $userID = null) {
    global $MainDatabase;;

    $message = $MainDatabase->real_escape_string($message);
    $file = $MainDatabase->real_escape_string($file);
    $line = $MainDatabase->real_escape_string($line);
    if ($userID) {
        $userID = $MainDatabase->real_escape_string($userID);
    }
    $time = time();

    if ($userID) {
        $MainDatabase->query("INSERT INTO errors(error, file, line, userid, created) VALUES ('$message', '$file', $line, '$userID', $time)");
    } else {
        $MainDatabase->query("INSERT INTO errors(error, file, line, created) VALUES ('$message', '$file', $line, $time)");
    }
}