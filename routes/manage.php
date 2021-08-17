<?php
/*
 * VIEWS
 */

$klein->respond('GET', '/department', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$me->IsHigherUp()) {
        $response->redirect("/");
        $response->send();
        die();
    }

    return $blade->make('page.department.index', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

$klein->respond('GET', '/department/[i:id]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $dep = new Department($request->id);
    if (!$dep->exists) {
        $response->redirect("/department", 404);
        $response->send();
        die();
    }

    if (!$dep->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.department.manage', ['me' => $me, 'steam' => $steam, 'config' => $config, 'rosterID' => $request->id])->render();
});

/*
 * POSTS
 */

$klein->respond('POST', '/department/[i:depID]/calendar/delete', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    if (!$_POST['event']) {
        $response->code(403);
        $response->send();
        die();
    }

    $meeting = new Meeting($_POST['event']);

    if (!$meeting->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $meeting->Delete();

    $me->CreateLog("Deleted a meeting with the ID " . $_POST['event'] . " for the department " . $department->GetName() . ".");

    $response->redirect("/department/" . $department->GetID(), 200);
});

$klein->respond('POST', '/department/[i:depID]/tag/create', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    $tag = new Tag();
    $tag->Create($request->depID, $_POST['name'], strtoupper($_POST['slug']), $_POST['color'], isset($_POST['expires']) ?($_POST['expires'] == "on") : false);

    $me->CreateLog("Created a tag called " . $_POST['name'] . " for the department " . $department->GetName() . ".");

    $response->redirect("/department/" . $department->GetID(), 200);
});

$klein->respond('POST', '/department/[i:depID]/tag/delete', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    if (!$_POST['tag']) {
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

    $tag->Delete();

    $me->CreateLog("Deleted the tag with ID " . $_POST['tag'] . " for the department " . $department->GetName() . ".");

    $response->redirect("/department/" . $department->GetID(), 200);
});

$klein->respond('POST', '/department/[i:depID]/announcement/create', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    // Validate title
    $title = $_POST['title'];
    if (!$title) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($title) < 4) $title = "No title given";
    if (strlen($title) > 64) $title = "No title given";

    // Validate title
    $content = $_POST['content'];
    if (!$content) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($content) < 4) $content = "No content given";
    if (strlen($content) > 1000) $content = "No content given";

    $department->CreateAnnouncement($title, $content, $me->GetSteamID64());

    $me->CreateLog("Created an announcement called " . $title . " for the department " . $department->GetName() . ".");

    $response->redirect("/department/" . $department->GetID(), 200);
});

$klein->respond('POST', '/department/[i:depID]/announcement/delete', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    // Validate title
    $announcementID = $_POST['announcement'];
    if (!$announcementID) {
        $response->code(403);
        $response->send();
        die();
    }

    $department->DeleteAnnouncement($announcementID, $me->GetSteamID64());

    $me->CreateLog("Deleted an announcement with the ID " . $announcementID . " for the department " . $department->GetName() . ".");

    $response->redirect("/department/" . $department->GetID(), 200);
});