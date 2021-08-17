<?php
/*
 * VIEWS
 */

$klein->respond('GET', '/calendar', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.calendar.index', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

/*
 * POSTS
 */

$klein->respond('POST', '/calendar/event/create', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = new Department($_POST['department']);
    if (!$department->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    // Validate title
    $title = $_POST['name'];
    if (!$title) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($title) < 2) $title = "No name given";
    if (strlen($title) > 32) $title = "No name given";


    $mandatory = isset($_POST['mandatory']) and ($_POST['mandatory'] == "on");

    $date = $_POST['calendar'];
    if (!$date) {
        $response->code(403);
        $response->send();
        die();
    }
    $date = intval($date);
    if (!$date) {
        $response->code(403);
        $response->send();
        die();
    }

    $allMeetings = (new Meeting())->GetAll();
    foreach($allMeetings as $meeting) {
        if (strval($meeting->GetTime()) == strval($date)) {
            $response->code(403);
            $response->redirect("/calendar", 200);
            $response->send();
            die();
        }
    }

    $meeting = new Meeting();
    $meeting->Create($title, $department->GetID(), $date, $mandatory, $me->GetSteamID64());


    $me->CreateLog("Created a meeting called " . $title . " for time " . $date);

    $response->redirect("/calendar", 200);
});