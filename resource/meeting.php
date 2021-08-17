<?php
function CreateMeeting($title, $depID, $date, $mandatory, $creator)
{
    global $MainDatabase;;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);
    $title = $MainDatabase->real_escape_string($title);
    $date = $MainDatabase->real_escape_string($date);
    $mandatory = $mandatory ? 1 : false;
    $creator = $MainDatabase->real_escape_string($creator);
    $time = time();

    if ($mandatory) {
        $result = $MainDatabase->query("INSERT INTO meetings(department_id, title, `time`, mandatory, userid, created) VALUES ('$depID', '$title', $date, '$mandatory', $creator, $time)");
    } else {
        $result = $MainDatabase->query("INSERT INTO meetings(department_id, title, `time`, userid, created) VALUES ('$depID', '$title', $date, $creator, $time)");
    }

    if (!$result) {
        echo $MainDatabase->error;
    }

    $meetingID = $MainDatabase->insert_id;

    $cache->remove("meeting-all");
    $cache->remove("meeting-dep-$depID");

    return $meetingID;
}

function DeleteMeeting($id, $depID) {
    global $MainDatabase;;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("UPDATE meetings SET deleted = '1' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("meeting-$id");
    $cache->remove("meeting-dep-$depID");
    $cache->remove("meeting-all");

    return true;
}
function GetMeeting($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("meeting-$id")) {
        return $cache->get("meeting-$id");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM meetings WHERE id = '$id' LIMIT 1;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("meeting-$id", $result, 600);

    return $result;
}

function GetAllMeetingsForDep($depID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("meeting-dep-$depID")) {
        return $cache->get("meeting-dep-$depID");
    }

    $depID = $MainDatabase->real_escape_string($depID);

    $result = $MainDatabase->query("SELECT * FROM meetings WHERE department_id = '$depID' AND deleted IS NULL;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("meeting-dep-$depID", $result, 60*60*24);

    return $result;
}

function GetAllMeetings() {
    global $MainDatabase;
    global $cache;

    if ($cache->has("meeting-all")) {
        return $cache->get("meeting-all");
    }

    $result = $MainDatabase->query("SELECT * FROM meetings WHERE deleted IS NULL;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("meeting-all", $result, 60*60*24);

    return $result;
}