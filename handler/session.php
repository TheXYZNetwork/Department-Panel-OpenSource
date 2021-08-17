<?php
function CreateSession($id, $key) {
    global $MainDatabase;

    $id = $MainDatabase->real_escape_string($id);
    $key = $MainDatabase->real_escape_string($key);
    $time = time();

    $MainDatabase->query("INSERT INTO sessions(userid, token, created) VALUES ('$id', '$key', $time)");
}

function GetSessionUser($key) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("token-$key")) {
        return $cache->get("token-$key");
    }

    $key = $MainDatabase->real_escape_string($key);

    $result = $MainDatabase->query("SELECT userid FROM sessions WHERE token = '$key' LIMIT 1");

    // Some kind of error
    if (!$result) return false;
    // No users with this info
    if ($result->num_rows < 1) return false;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("token-$key", $result['userid'], 600);

    return $result['userid'];
}

function GetAllUserSessions($userID) {
    global $MainDatabase;

    $userID = $MainDatabase->real_escape_string($userID);

    $result = $MainDatabase->query("SELECT token FROM sessions WHERE userid = '$userID'");

    // Some kind of error
    if (!$result) return false;
    // No users with this info
    if ($result->num_rows < 1) return false;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    return $result;
}

function ClearUserSessions($userID) {
    global $MainDatabase;
    global $cache;

    $activeSessions = GetAllUserSessions($userID);

    if (empty($activeSessions)) return;

    foreach($activeSessions as $session) {
        $cache->remove("token-" . $session['token']);
    }

    $userID = $MainDatabase->real_escape_string($userID);
    $MainDatabase->query("DELETE FROM sessions WHERE userid = '$userID'");

}