<?php
require_once("handler/database.php");

function CreateAuditLog($userID, $log) {
    global $MainDatabase;
    global $cache;

    $userID = $MainDatabase->real_escape_string($userID);
    $log = $MainDatabase->real_escape_string($log);
    $time = time();

    $MainDatabase->query("INSERT INTO audit_logs(userid, log, created) VALUES ('$userID', '$log', $time)");

    $cache->remove("audit-recent");
}

function GetRecentAuditLogs() {
    global $MainDatabase;
    global $cache;

    if ($cache->has("audit-recent")) {
        return $cache->get("audit-recent");
    }

    $result = $MainDatabase->query("SELECT * FROM audit_logs ORDER BY created DESC LIMIT 100;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("audit-recent", $result, 600);

    return $result;
}