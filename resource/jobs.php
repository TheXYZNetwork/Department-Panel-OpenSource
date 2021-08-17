<?php
require_once("handler/database.php");

function GetJob($class) {
    global $xWhitelistDatabase;
    global $cache;

    if ($cache->has("job-$class")) {
        return $cache->get("job-$class");
    }

    $class = $xWhitelistDatabase->real_escape_string($class);

    $result = $xWhitelistDatabase->query("SELECT * FROM pol1_job_info WHERE job = '$class' LIMIT 1");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_array(MYSQLI_ASSOC);

    $cache->store("job-$class", $result, 60*60);

    return $result;
}
function GetAllJobs() {
    global $xWhitelistDatabase;
    global $cache;

    if ($cache->has("job-all")) {
        return $cache->get("job-all");
    }

    $result = $xWhitelistDatabase->query("SELECT * FROM pol1_job_info");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("job-all", $result, 60*60);

    return $result;
}
function GetJobMembers($class) {
    global $xWhitelistDatabase;
    global $cache;

    if ($cache->has("job-$class-members")) {
        return $cache->get("job-$class-members");
    }

    $class = $xWhitelistDatabase->real_escape_string($class);

    $result = $xWhitelistDatabase->query("SELECT * FROM pol1_whitelist WHERE job='$class'");

    // Some kind of error
    if (!$result) return;
    // No users with this info
    if ($result->num_rows < 1) return;
    $result = $result->fetch_all(MYSQLI_ASSOC);

    $cache->store("job-$class-members", $result, 600);

    return $result;
}