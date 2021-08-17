<?php
require_once("handler/database.php");

function CreateUser($id, $name, $avatar) {
    global $MainDatabase;

    $id = $MainDatabase->real_escape_string($id);
    $name = $MainDatabase->real_escape_string($name);
    $avatar = $MainDatabase->real_escape_string($avatar);
    $time = time();

    $MainDatabase->query("INSERT INTO users(userid, name, avatar, lastseen, joined) VALUES ('$id', '$name', '$avatar', $time, $time)");
}
function GetUser($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("user-$id")) {
        return $cache->get("user-$id");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM users WHERE userid = '$id' LIMIT 1");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("user-$id", $result, 600);

    return $result;
}
function SetUserLastSeen($id) {
    global $MainDatabase;

    $id = $MainDatabase->real_escape_string($id);
    $time = time();

    $MainDatabase->query("UPDATE users SET lastseen='$time' WHERE userid='$id'");
}
function UpdateUserBan($id, $state) {
    global $MainDatabase;

    $id = $MainDatabase->real_escape_string($id);
    $state = $state ? 1 : 0;

    $MainDatabase->query("UPDATE users SET banned='$state' WHERE userid='$id'");
}
function GetUserGroup($id) {
    global $xAdminDatabse;
    global $cache;

    if ($cache->has("user-$id-usergroup")) {
        return $cache->get("user-$id-usergroup");
    }

    $id = $xAdminDatabse->real_escape_string($id);

    $result = $xAdminDatabse->query("SELECT rank FROM pol1_users WHERE userid='$id' LIMIT 1");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("user-$id-usergroup", $result['rank'], 600);

    return $result['rank'];
}