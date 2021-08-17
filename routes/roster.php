<?php
/*
 * VIEWS
 */

$klein->respond('GET', '/roster', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    return $blade->make('page.roster.index', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});
$klein->respond('GET', '/roster/[i:id]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    $dep = new Department($request->id);
    if (!$dep->exists) {
        $response->redirect("/roster", 404);
        $response->send();
        die();
    }

    return $blade->make('page.roster.table', ['me' => $me, 'steam' => $steam, 'config' => $config, 'rosterID' => $request->id])->render();
});


/*
 * POSTS
 */
$klein->respond('POST', '/roster/[i:depID]/logs/[i:logID]/comment/add', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($request->depID);

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    AddMemberLogComment($request->logID, $me->GetSteamID64(), $_POST['comment']);

    $me->CreateLog("Added a comment for a log for the department " . $department->GetName() . ". The log ID is " . $request->logID . " and the comment was: " . $_POST['comment']);

    $response->redirect("/roster/" . $request->depID, 200);
});

$klein->respond('POST', '/roster/[i:depID]/activity/[i:activityID]/comment/add', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($request->depID);

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    AddMemberActivityComment($request->activityID, $me->GetSteamID64(), $_POST['comment']);

    $me->CreateLog("Added a comment for a activity for the department " . $department->GetName() . ". The activity ID is " . $request->activityID . " and the comment was: " . $_POST['comment']);

    $response->redirect("/roster/" . $request->depID, 200);
});

$klein->respond('POST', '/roster/[i:depID]/tag/give', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($request->depID);

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$_POST['userid']) {
        $response->code(403);
        $response->send();
        die();
    }

    $member = $department->GetMember($_POST['userid']);

    if (!$member) {
        $response->code(403);
        $response->send();
        die();
    }

    $tag = new Tag($_POST['tag']);

    if (!$tag->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    if ($member->HasTag($tag)) {
        $response->code(403);
        $response->redirect("/roster/" . $request->depID, 200);
        $response->send();
        die();
    }

    if ($tag->Expires() and (!isset($_POST['expire']) or ($_POST['expire'] == ""))) {
        $_POST['expire'] = time() + (60*60*24*7); // If a time is needed but not provided, default it to 7 days.
    } elseif ($tag->Expires()) {
        $_POST['expire'] = strtotime($_POST['expire']);
    } else {
        $_POST['expire'] = false;
    }

    $member->GiveTag($tag, $_POST['expire']);

    $me->CreateLog("Gave the SteamID " . $_POST['userid'] . " the tag " . $tag->GetName() . " for the department " . $department->GetName() . ".");

    $response->redirect("/roster/" . $request->depID, 200);
});

$klein->respond('POST', '/roster/[i:depID]/[i:userID]/tag/[i:tagID]/remove', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($request->depID);

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    $member = $department->GetMember($request->userID);

    if (!$member or !$member->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $tag = new Tag($request->tagID);

    if (!$tag->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$member->HasTag($tag)) {
        $response->code(403);
        $response->send();
        die();
    }

    $member->RemoveTag($tag);

    $me->CreateLog("Removed the SteamID " . $request->userID . "'s tag named " . $tag->GetName() . " for the department " . $department->GetName() . ".");

    $response->redirect("/roster/" . $request->depID, 200);
});

$klein->respond('POST', '/roster/[i:depID]/points/give', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($request->depID);

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$_POST['userid']) {
        $response->code(403);
        $response->send();
        die();
    }

    $member = $department->GetMember($_POST['userid']);

    if (!$member) {
        $response->code(403);
        $response->send();
        die();
    }

    $date = $_POST['date'];
    if (isset($_POST['expires']) and ($_POST['expires'] == "on") and isset($date)) {
        $date = strtotime($date);
    } elseif (isset($_POST['expires']) and ($_POST['expires'] == "on")) {
        $date = time() + (60*60*24*7); // If a time is needed but not provided, default it to 7 days.
    } else {
        $date = false;
    }

    // Validate amount
    $amount = $_POST['amount'];
    if (!$amount) {
        $response->code(403);
        $response->send();
        die();
    }
    if (!intval($amount)) $amount = 10;
    $amount = round($amount);
    if ($amount > 100) $amount = 100;
    if ($amount < 10) $amount = 10;

    // Validate reason
    $reason = $_POST['reason'];
    if (!$reason) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($reason) < 6) $reason = "No reason given";
    if (strlen($reason) > 60) $reason = "No reason given";

    $member->GivePoints($department, $amount, $reason, $date, $me);

    $me->CreateLog("Gave the SteamID " . $_POST['userid'] . " " . $amount . " points with the reason '" . $reason . "' for the department " . $department->GetName() . ".");

    $response->redirect("/roster/" . $request->depID, 200);
});

$klein->respond('POST', '/roster/[i:depID]/[i:userID]/points/[i:pointsID]/remove', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($request->depID);

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    $member = $department->GetMember($request->userID);

    if (!$member or !$member->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $points = new Points($request->pointsID);

    if (!$points->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $points->Delete();

    $me->CreateLog("Removed the SteamID " . $request->userid . "'s points for the department " . $department->GetName() . ".");

    $response->redirect("/roster/" . $request->depID, 200);
});

$klein->respond('POST', '/roster/[i:depID]/points/[i:pointsID]/comment/add', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($request->depID);

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    AddMemberPointsComment($request->pointsID, $me->GetSteamID64(), $_POST['comment']);

    $me->CreateLog("Added a comment for a point for the department " . $department->GetName() . ". The point ID is " . $request->pointsID . " and the comment was: " . $_POST['comment']);

    $response->redirect("/roster/" . $request->depID, 200);
});