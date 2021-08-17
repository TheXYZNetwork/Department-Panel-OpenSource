<?php
require_once("handler/database.php");

function CreateTag($depID, $name, $slug, $color, $expires) {
    global $MainDatabase;;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);
    $name = $MainDatabase->real_escape_string($name);
    $slug = $MainDatabase->real_escape_string($slug);
    $color = $MainDatabase->real_escape_string($color);
    $expires = $expires ? 1 : false;
    $time = time();

    if ($expires) {
        $result = $MainDatabase->query("INSERT INTO departments_tags(department_id, name, slug, color, expires, created, modified) VALUES ($depID, '$name', '$slug', '$color', $expires, $time, $time)");
    } else {
        $result = $MainDatabase->query("INSERT INTO departments_tags(department_id, name, slug, color, created, modified) VALUES ($depID, '$name', '$slug', '$color', $time, $time)");
    }

    if (!$result) {
        echo $MainDatabase->error;
    }

    $tagID = $MainDatabase->insert_id;

    $cache->remove("tag-all");

    return $tagID;
}

function GetTag($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("tag-$id")) {
        return $cache->get("tag-$id");
    }

    $id = $MainDatabase->real_escape_string($id);


    $result = $MainDatabase->query("SELECT * FROM departments_tags WHERE id = '$id' LIMIT 1;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("tag-$id", $result, 600);

    return $result;
}

function GetAllTags() {
    global $MainDatabase;
    global $cache;

    if ($cache->has("tag-all")) {
        return $cache->get("tag-all");
    }

    $result = $MainDatabase->query("SELECT * FROM departments_tags WHERE deleted IS NULL");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("tag-all", $result, 600);

    return $result;
}
function DeleteTag($id) {
    global $MainDatabase;;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $time = time();

    $result = $MainDatabase->query("UPDATE departments_tags SET deleted = '1', modified='$time' WHERE id = $id");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("tag-$id");
    $cache->remove("tag-all");

    return true;
}

function GiveMemberTag($userID, $tagID, $depID, $expires) {
    global $MainDatabase;;
    global $cache;

    $userID = $MainDatabase->real_escape_string($userID);
    $tagID = $MainDatabase->real_escape_string($tagID);
    $depID = $MainDatabase->real_escape_string($depID);
    $expires = $expires ? $MainDatabase->real_escape_string($expires) : false;
    $time = time();


    if ($expires) {
        $result = $MainDatabase->query("INSERT INTO members_tags(userid, department_id, tag_id, expires, created) VALUES ('$userID', '$depID', '$tagID', '$expires', $time)");
    } else {
        $result = $MainDatabase->query("INSERT INTO members_tags(userid, department_id, tag_id, created) VALUES ('$userID', '$depID', '$tagID', $time)");
    }

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("member-$userID-tags-$depID");
    $cache->remove("tag-members-$tagID");

    return true;
}
function GetMembersTags($userID, $depID) {
    global $MainDatabase;;
    global $cache;

    if ($cache->has("member-$userID-tags-$depID")) {
        return $cache->get("member-$userID-tags-$depID");
    }

    $userID = $MainDatabase->real_escape_string($userID);
    $depID = $MainDatabase->real_escape_string($depID);


    $result = $MainDatabase->query("SELECT * FROM members_tags WHERE userid = '$userID' AND department_id = '$depID' ORDER BY tag_id");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("member-$userID-tags-$depID", $result);

    return $result;
}

function RemoveMemberTag($userID, $tagID, $depID) {
    global $MainDatabase;;
    global $cache;

    $userID = $MainDatabase->real_escape_string($userID);
    $tagID = $MainDatabase->real_escape_string($tagID);
    $depID = $MainDatabase->real_escape_string($depID);


    $result = $MainDatabase->query("DELETE FROM members_tags WHERE userid = '$userID' AND department_id = '$depID' AND tag_id = '$tagID'");

    if (!$result) {
        echo $MainDatabase->error;
    }

    $cache->remove("member-$userID-tags-$depID");

    return true;
}

function GetExpiredTags() {
    global $MainDatabase;;
    global $cache;

    $time = time();

    $result = $MainDatabase->query("SELECT * FROM members_tags WHERE expires < $time");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    return $result;
}
function GetTagsMembers($tagID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("tag-members-$tagID")) {
        return $cache->get("tag-members-$tagID");
    }

    $tagID = $MainDatabase->real_escape_string($tagID);

    $result = $MainDatabase->query("SELECT * FROM members_tags WHERE tag_id='$tagID';");

    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("tag-members-$tagID", $result, 600);

    return $result;
}