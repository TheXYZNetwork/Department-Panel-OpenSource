<?php
require_once("handler/database.php");


function GetMemberInfo($steam64) {
    global $GameDatabase;
    global $cache;

    if ($cache->has("member-$steam64")) {
        return $cache->get("member-$steam64");
    }

    $steam64 = $GameDatabase->real_escape_string($steam64);

    $result = $GameDatabase->query("SELECT * FROM darkrp_player WHERE uid = '$steam64' LIMIT 1");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("member-$steam64", $result, 600);

    return $result;
}
function GetMemberActivity($steam64, $identifier) {
    global $GameDatabase;
    global $cache;

    if ($cache->has("member-$steam64-activity-$identifier")) {
        return $cache->get("member-$steam64-activity-$identifier");
    }

    $steam64 = $GameDatabase->real_escape_string($steam64);
    $identifier = $GameDatabase->real_escape_string($identifier);

    $result = $GameDatabase->query("SELECT * FROM job_tracker WHERE userid = '$steam64' AND jobType = '$identifier' ORDER BY `join` DESC LIMIT 20");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("member-$steam64-activity-$identifier", $result, 600);

    return $result;
}
function GetMemberLogs($steam64, $identifier) {
    global $GameDatabase;
    global $cache;

    if ($cache->has("member-$steam64-logs-$identifier")) {
        return $cache->get("member-$steam64-logs-$identifier");
    }

    $steam64 = $GameDatabase->real_escape_string($steam64);
    $identifier = $GameDatabase->real_escape_string($identifier);

    $result = $GameDatabase->query("SELECT * FROM job_tracker_promo WHERE userid = '$steam64' AND jobType = '$identifier' ORDER BY `time` DESC LIMIT 20");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("member-$steam64-logs-$identifier", $result, 600);

    return $result;
}
function AddMemberLogComment($logID, $commenter, $comment) {
    global $MainDatabase;
    global $cache;

    $logID = $MainDatabase->real_escape_string($logID);
    $commenter = $MainDatabase->real_escape_string($commenter);
    $comment = $MainDatabase->real_escape_string($comment);
    $time = time();

    $MainDatabase->query("INSERT INTO comments_logs(log_id, userid, comment, created) VALUES ($logID, '$commenter', '$comment', $time)");

    $cache->remove("comments-logs-$logID");
}
function GetCommentsForLog($logID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("comments-logs-$logID")) {
        return $cache->get("comments-logs-$logID");
    }

    $logID = $MainDatabase->real_escape_string($logID);

    $result = $MainDatabase->query("SELECT * FROM comments_logs WHERE log_id='$logID'");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("comments-logs-$logID", $result, 600);

    return $result;
}
function AddMemberActivityComment($activityID, $commenter, $comment) {
    global $MainDatabase;
    global $cache;

    $activityID = $MainDatabase->real_escape_string($activityID);
    $commenter = $MainDatabase->real_escape_string($commenter);
    $comment = $MainDatabase->real_escape_string($comment);
    $time = time();

    $MainDatabase->query("INSERT INTO comments_activity(activity_id, userid, comment, created) VALUES ($activityID, '$commenter', '$comment', $time)");

    $cache->remove("comments-activity-$activityID");
}
function GetCommentsForActivity($activityID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("comments-activity-$activityID")) {
        return $cache->get("comments-activity-$activityID");
    }

    $activityID = $MainDatabase->real_escape_string($activityID);

    $result = $MainDatabase->query("SELECT * FROM comments_activity WHERE activity_id='$activityID'");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("comments-activity-$activityID", $result, 600);

    return $result;
}
function AddMemberPointsComment($pointsID, $commenter, $comment) {
    global $MainDatabase;
    global $cache;

    $activityID = $MainDatabase->real_escape_string($pointsID);
    $commenter = $MainDatabase->real_escape_string($commenter);
    $comment = $MainDatabase->real_escape_string($comment);
    $time = time();

    $MainDatabase->query("INSERT INTO comments_points(points_id, userid, comment, created) VALUES ($pointsID, '$commenter', '$comment', $time)");

    $cache->remove("comments-points-$activityID");
}
function GetCommentsForPoints($pointsID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("comments-points-$pointsID")) {
        return $cache->get("comments-points-$pointsID");
    }

    $activityID = $MainDatabase->real_escape_string($pointsID);

    $result = $MainDatabase->query("SELECT * FROM comments_points WHERE points_id='$pointsID'");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("comments-points-$pointsID", $result, 600);

    return $result;
}