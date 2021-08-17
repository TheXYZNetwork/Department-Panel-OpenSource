<?php
require_once("handler/database.php");

function CreateDepartment($name, $jobs, $isGovernment, $identifier) {
    global $MainDatabase;;
    global $cache;

    $name = $MainDatabase->real_escape_string($name);
    $isGovernment = $isGovernment ? 1 : false;
    $identifier = $identifier or false;
    $name = $MainDatabase->real_escape_string($name);
    $time = time();

    if ($isGovernment and $identifier) {
        $result = $MainDatabase->query("INSERT INTO departments(name, government, identifier, created, modified) VALUES ('$name', $isGovernment, '$identifier', $time, $time)");
    } elseif ($isGovernment) {
        $result = $MainDatabase->query("INSERT INTO departments(name, government, created, modified) VALUES ('$name', $isGovernment, $time, $time)");
    } elseif ($identifier) {
        $result = $MainDatabase->query("INSERT INTO departments(name, identifier, created, modified) VALUES ('$name', '$identifier', $time, $time)");
    } else {
        $result = $MainDatabase->query("INSERT INTO departments(name, created, modified) VALUES ('$name', $time, $time)");
    }

    if (!$result) {
        echo $MainDatabase->error;
    }

    $depID = $MainDatabase->insert_id;

    foreach($jobs as $order => $job) {
        AddJobToDepartment($depID, $job, $order);
    }

    $cache->remove('department-all');

    return $depID;
}
function AddJobToDepartment($depID, $job, $sorting) {
    global $MainDatabase;

    $depID = $MainDatabase->real_escape_string($depID);
    $job = $MainDatabase->real_escape_string($job);
    $sorting = $MainDatabase->real_escape_string($sorting);
    $time = time();

    $MainDatabase->query("UPDATE departments_jobs SET sorting = sorting + 1 WHERE department_id = '$depID' AND sorting >= $sorting;");
    $MainDatabase->query("INSERT INTO departments_jobs(department_id, job, sorting, created) VALUES ('$depID', '$job', $sorting, $time);");
}
function RemoveJobFromDepartment($depID, $job, $sorting) {
    global $MainDatabase;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);
    $job = $MainDatabase->real_escape_string($job);
    $sorting = $MainDatabase->real_escape_string($sorting);

    $MainDatabase->query("UPDATE departments_jobs SET sorting = sorting - 1 WHERE department_id = '$depID' AND sorting >= $sorting;");
    $MainDatabase->query("DELETE FROM departments_jobs WHERE department_id='$depID' AND job='$job';");

    $cache->remove("department-$depID-jobs");
}
function RenameDepartment($id, $name) {
    global $MainDatabase;;
    global $cache;

    $id = $MainDatabase->real_escape_string($id);
    $name = $MainDatabase->real_escape_string($name);
    $time = time();

    $MainDatabase->query("UPDATE departments SET name = '$name', modified = '$time' WHERE id = $id");

    $cache->remove("department-$id");
    $cache->remove("department-all");

    return true;
}
function UpdateDepartmentIdentifier($depID, $userID, $identifier) {
    global $MainDatabase;;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);
    if ($identifier != false) {
        $identifier = $MainDatabase->real_escape_string($identifier);
    }
    $time = time();

    if ($identifier) {
        $MainDatabase->query("UPDATE departments SET identifier = '$identifier', modified = '$time' WHERE id = $depID");
    } else {
        $MainDatabase->query("UPDATE departments SET identifier = NULL, modified = '$time' WHERE id = $depID");
    }

    $cache->remove("department-$depID");
    $cache->remove("department-all");

    return true;
}

function GetDepartment($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("department-$id")) {
        return $cache->get("department-$id");
    }

    $id = $MainDatabase->real_escape_string($id);


    $result = $MainDatabase->query("SELECT * FROM departments WHERE id = '$id' LIMIT 1;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("department-$id", $result, 600);

    return $result;
}
function GetDepartmentJobs($id) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("department-$id-jobs")) {
        return $cache->get("department-$id-jobs");
    }

    $id = $MainDatabase->real_escape_string($id);

    $result = $MainDatabase->query("SELECT * FROM departments_jobs WHERE department_id = '$id' ORDER BY sorting;");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("department-$id-jobs", $result, 600);

    return $result;
}
function GetAllDepartments() {
    global $MainDatabase;
    global $cache;

    if ($cache->has("department-all")) {
        return $cache->get("department-all");
    }


    $result = $MainDatabase->query("SELECT * FROM departments WHERE deleted IS NULL");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("department-all", $result, 600);

    return $result;
}
function ReorderJobInDepartment($jobID, $order, $depID) {
    global $MainDatabase;
    global $cache;

    $jobID = $MainDatabase->real_escape_string($jobID);
    $order = $MainDatabase->real_escape_string($order);

    $MainDatabase->query("SELECT @old_sorting:=sorting FROM departments_jobs WHERE id = '$jobID';");
    $MainDatabase->query("UPDATE departments_jobs SET sorting = IF(id = '$jobID', $order, sorting + IF(@old_sorting > $order, 1, -1)) WHERE sorting BETWEEN LEAST(@old_sorting, $order) AND GREATEST(@old_sorting, $order);");

    $cache->remove("department-$depID-jobs");
}
function ClearDepartmentHigherUps($depID) {
    global $MainDatabase;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);

    $MainDatabase->query("UPDATE departments_jobs SET higherup = NULL WHERE department_id = '$depID';");

    $cache->remove("department-$depID-jobs");
}
function AddDepartmentHigherUps($jobID, $depID) {
    global $MainDatabase;
    global $cache;

    $jobID = $MainDatabase->real_escape_string($jobID);

    $MainDatabase->query("UPDATE departments_jobs SET higherup = 1 WHERE id = '$jobID';");

    $cache->remove("department-$depID-jobs");
}

function CreateDepartmentAnnouncement($depID, $title, $content, $userID) {
    global $MainDatabase;
    global $cache;

    $depID = $MainDatabase->real_escape_string($depID);
    $title = $MainDatabase->real_escape_string($title);
    $content = $MainDatabase->real_escape_string($content);
    $userID = $MainDatabase->real_escape_string($userID);
    $time = time();

    $MainDatabase->query("INSERT INTO departments_announcements(department_id, title, `desc`, userid, created) VALUES ('$depID', '$title', '$content', '$userID', $time)");

    $cache->remove("department-$depID-announcements");
}

function DeleteDepartmentAnnouncement($depID, $announcementID, $userID) {
    global $MainDatabase;
    global $cache;

    $announcementID = $MainDatabase->real_escape_string($announcementID);

    $MainDatabase->query("UPDATE departments_announcements SET deleted = 1 WHERE id = '$announcementID';");

    $cache->remove("department-$depID-announcements");
}

function GetRecentDepartmentAnnouncement($depID) {
    global $MainDatabase;
    global $cache;

    if ($cache->has("department-$depID-announcements")) {
        return $cache->get("department-$depID-announcements");
    }

    $depID = $MainDatabase->real_escape_string($depID);

    $result = $MainDatabase->query("SELECT * FROM departments_announcements WHERE department_id='$depID' AND (deleted <> '1' OR deleted IS NULL) LIMIT 10");


    // Some kind of error
    if (!$result) return [];
    // No users with this info
    if ($result->num_rows < 1) return [];
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("department-$depID-announcements", $result, 600);

    return $result;
}

function GetRecentDepartmentActivity($flag) {
    global $GameDatabase;
    global $cache;

    if ($cache->has("department-$flag-activity-recent")) {
        return $cache->get("department-$flag-activity-recent");
    }

    $flag = $GameDatabase->real_escape_string($flag);
    $now = time();
    $aWeekAgo = $now - (60*60*24*7);


    $result = $GameDatabase->query("SELECT SUM(`leave` - `join`) AS DIFF FROM job_tracker WHERE jobType='$flag' AND (`join` > $aWeekAgo) AND `leave` IS NOT NULL;");

    // Some kind of error
    if (!$result) return 3;
    // No users with this info
    if ($result->num_rows < 1) return 2;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("department-$flag-activity-recent", $result['DIFF'], 600);

    return $result['DIFF'];
}