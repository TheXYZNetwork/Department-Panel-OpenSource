<?php

function GetPoints($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("points-$id")) {
        return $cache->get("points-$id");
    }

    $id = $MainDatabase->real_escape_string($id);


    $result = $MainDatabase->query("SELECT * FROM members_points WHERE id = '$id' LIMIT 1;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("points-$id", $result, 600);

    return $result;
}
function GetMemberPoints($userID, $depID) {
    global $MainDatabase;;
    global $cache;

    if ($cache->has("member-$userID-points-$depID")) {
        return $cache->get("member-$userID-points-$depID");
    }

    $userID = $MainDatabase->real_escape_string($userID);
    $depID = $MainDatabase->real_escape_string($depID);


    $result = $MainDatabase->query("SELECT * FROM members_points WHERE userid = '$userID' AND department_id = '$depID' AND deleted IS NULL ORDER BY created");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("member-$userID-points-$depID", $result);

    return $result;
}

function GiveMemberPoints($userID, $depID, $officerID, $amount, $reason, $expires) {
    global $MainDatabase;;
    global $cache;

    $userID = $MainDatabase->real_escape_string($userID);
    $depID = $MainDatabase->real_escape_string($depID);
    $officerID = $MainDatabase->real_escape_string($officerID);
    $amount = $MainDatabase->real_escape_string($amount);
    $reason = $MainDatabase->real_escape_string($reason);
    $expires = $expires ? $MainDatabase->real_escape_string($expires) : false;
    $time = time();


    if ($expires) {
        $result = $MainDatabase->query("INSERT INTO members_points(userid, department_id, officer_id, amount, reason, expires, created) VALUES ('$userID', '$depID', '$officerID', '$amount', '$reason', '$expires', $time)");
    } else {
        $result = $MainDatabase->query("INSERT INTO members_points(userid, department_id, officer_id, amount, reason, created) VALUES ('$userID', '$depID', '$officerID', '$amount', '$reason', $time)");
    }

    if (!$result) {
        echo $MainDatabase->error;
    }

    $tagID = $MainDatabase->insert_id;

    $cache->remove("member-$userID-points-$depID");

    return true;
}

function DeletePoints($pointsID, $userID, $depID) {
    global $MainDatabase;;
    global $cache;

    $pointsID = $MainDatabase->real_escape_string($pointsID);

    $result = $MainDatabase->query("UPDATE members_points SET deleted = '1' WHERE id = $pointsID");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("member-$userID-points-$depID");

    return true;
}
