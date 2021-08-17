<?php
require_once("handler/database.php");

function CreateForm($depID, $title, $desc, $viewability, $responseViewability, $creator) {
    global $MainDatabase;;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);
    $title = $MainDatabase->real_escape_string($title);
    $desc = $MainDatabase->real_escape_string($desc);
    $viewability = json_encode($viewability);
    $viewability = $MainDatabase->real_escape_string($viewability);
    $responseViewability = json_encode($responseViewability);
    $responseViewability = $MainDatabase->real_escape_string($responseViewability);
    $creator = $MainDatabase->real_escape_string($creator);
    $time = time();


    $result = $MainDatabase->query("INSERT INTO forms(userid, department_id, title, description, viewability, viewability_responses, created) VALUES ('$creator', $depID, '$title', '$desc', '$viewability', '$responseViewability', $time)");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $formID = $MainDatabase->insert_id;

    $cache->remove("forms-all");
    $cache->remove("forms-dep-$depID");

    return $formID;
}

function AddFormElement($formID, $type, $title, $sorting, $data = null) {
    global $MainDatabase;;
    global $cache;

    $formID = $MainDatabase->real_escape_string($formID);
    $type = $MainDatabase->real_escape_string($type);
    $title = $MainDatabase->real_escape_string($title);
    $sorting = $MainDatabase->real_escape_string($sorting);
    $data = $data ? $MainDatabase->real_escape_string(json_encode($data)) : false;
    $time = time();

    $MainDatabase->query("UPDATE forms_elements SET sorting = sorting + 1 WHERE form_id = '$formID' AND sorting >= $sorting;");
    $MainDatabase->query("INSERT INTO forms_elements(form_id, `type`, title, `data`, sorting, created) VALUES ('$formID', '$type', '$title', '$data', $sorting, $time)");

    $cache->remove("forms-$formID");
    $cache->remove("forms-all");
}
function ClearFormElements($formID) {
    global $MainDatabase;;
    global $cache;

    $formID = $MainDatabase->real_escape_string($formID);

    $MainDatabase->query("DELETE FROM forms_elements WHERE form_id = '$formID'");

    $cache->remove("forms-$formID-elements");
    $cache->remove("forms-all");
}

function GetForm($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("forms-$id")) {
        return $cache->get("forms-$id");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM forms WHERE id = '$id' LIMIT 1;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("forms-$id", $result, 600);

    return $result;
}

function GetFormElements($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("forms-$id-elements")) {
        return $cache->get("forms-$id-elements");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM forms_elements WHERE form_id = '$id' ORDER BY sorting;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("forms-$id-elements", $result, 600);

    return $result;
}

function GetAllFormsForDepartment($depID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("forms-dep-$depID")) {
        return $cache->get("forms-dep-$depID");
    }

    $id = $MainDatabase->real_escape_string($depID);

    $result = $MainDatabase->query("SELECT id FROM forms WHERE department_id = '$depID' AND (deleted <> '1' OR deleted IS NULL);");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("forms-dep-$id", $result, 600);

    return $result;
}

function UpdateFormTitle($id, $userID, $title) {
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $title = $MainDatabase->real_escape_string($title);
    $time = time();

    $result = $MainDatabase->query("UPDATE forms SET title = '$title', modified = '$time' WHERE id = $id");

    $cache->remove("forms-$id");
}

function UpdateFormDesc($id, $userID, $desc) {
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $desc = $MainDatabase->real_escape_string($desc);
    $time = time();

    $result = $MainDatabase->query("UPDATE forms SET description = '$desc', modified = '$time' WHERE id = $id");

    $cache->remove("forms-$id");
}

function UpdateFormPublished($id, $userID, $publish)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $publish = $publish ? 1 : 0;
    $time = time();

    $result = $MainDatabase->query("UPDATE forms SET published = '$publish', modified = '$time' WHERE id = $id");

    $cache->remove("forms-$id");

    return true;
}

function UpdateFormViewability($id, $userID, $viewability)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $viewability = json_encode($viewability);
    $viewability = $MainDatabase->real_escape_string($viewability);
    $time = time();

    $MainDatabase->query("UPDATE forms SET viewability = '$viewability', modified = '$time' WHERE id = $id");

    $cache->remove("forms-$id");
}

function UpdateFormResponseViewability($id, $userID, $responseViewability)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $responseViewability = json_encode($responseViewability);
    $responseViewability = $MainDatabase->real_escape_string($responseViewability);
    $time = time();

    $MainDatabase->query("UPDATE forms SET viewability_responses = '$responseViewability', modified = '$time' WHERE id = $id");

    $cache->remove("forms-$id");
}

function UpdateFormDeleted($id, $userID, $deleted, $depID)
{
    global $MainDatabase;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $deleted = $deleted ? 1 : 0;
    $time = time();

    $MainDatabase->query("UPDATE forms SET deleted = '$deleted', modified = '$time' WHERE id = $id");

    $cache->remove("forms-$id");
    $cache->remove("forms-dep-$depID");

    return true;
}

