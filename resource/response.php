<?php

function CreateFormResponse($formID, $userID) {
    global $MainDatabase;;
    global $cache;

    $formID = $MainDatabase->real_escape_string($formID);
    $userID = $MainDatabase->real_escape_string($userID);
    $time = time();


    $result = $MainDatabase->query("INSERT INTO forms_response(form_id, userid, created) VALUES ('$formID', '$userID', $time)");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $responseID = $MainDatabase->insert_id;

    $cache->remove("response-all");
    $cache->remove("response-form-$formID");

    return $responseID;
}

function CreateFormResponseAnswer($responseID, $type, $question, $answer) {
    global $MainDatabase;;
    global $cache;

    $responseID = $MainDatabase->real_escape_string($responseID);
    $type = $MainDatabase->real_escape_string($type);
    $question = $MainDatabase->real_escape_string($question);
    if(is_array($answer)) {
        $answer = json_encode($answer);
    };
    $answer = $MainDatabase->real_escape_string($answer);

    $MainDatabase->query("INSERT INTO forms_response_answers(response_id, type, question, answer) VALUES ('$responseID', '$type', '$question', '$answer')");


    $cache->remove("response-all");
    $cache->remove("response-$responseID-answers");
}

function GetAllResponsesForForm($formID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("response-form-$formID")) {
        return $cache->get("response-form-$formID");
    }

    $id = $MainDatabase->real_escape_string($formID);

    $result = $MainDatabase->query("SELECT * FROM forms_response WHERE form_id = '$id';");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("response-form-$formID", $result, 600);

    return $result;
}

function GetFormResponse($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("response-$id")) {
        return $cache->get("response-$id");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM forms_response WHERE id = '$id' LIMIT 1;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("response-$id", $result, 600);

    return $result;
}

function GetFormResponseAnswers($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("response-$id-answers")) {
        return $cache->get("response-$id-answers");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM forms_response_answers WHERE response_id = '$id';");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("response-$id-answers", $result, 600);

    return $result;
}

function UpdateFormResponseArchived($id, $userID, $archived)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $archived = $archived ? 1 : 0;

    $MainDatabase->query("UPDATE forms_response SET archived = '$archived' WHERE id = $id");

    $cache->remove("response-$id");

    return true;
}