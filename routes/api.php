<?php
$klein->respond('GET', '/api/logs/[i:steamID]/[i:depID]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    $member = $department->GetMember($request->steamID);

    $response->header("Content-Type", "application/json");

    if (!$member) {
        echo json_encode(['error' => 'No member found with that SteamID.']);
        $response->send();
        die();
    }

    $logs = $member->GetLogs();
    foreach($logs as $logKey => $log) {
        if (!isset($log['id'])) continue;

        $comments = GetCommentsForLog($log['id']);
        foreach($comments as $commentKey => $comment) {
            $user = new User($comment['userid']);
            $userData = $user->GetDataAsArray();

            $comments[$commentKey]['user'] = $userData;
        }

        $logs[$logKey]['comments'] = $comments;
    }


    echo json_encode($logs);
    $response->send();
});
$klein->respond('GET', '/api/activity/[i:steamID]/[i:depID]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    $member = $department->GetMember($request->steamID);

    $response->header("Content-Type", "application/json");

    if (!$member) {
        echo json_encode(['error' => 'No member found with that SteamID.']);
        $response->send();
        die();
    }

    $activity = $member->GetActivity();
    foreach($activity as $actKey => $act) {
        $comments = GetCommentsForActivity($act['id']);
        foreach($comments as $commentKey => $comment) {
            $user = new User($comment['userid']);
            $userData = $user->GetDataAsArray();

            $comments[$commentKey]['user'] = $userData;
        }

        $activity[$actKey]['comments'] = $comments;
    }


    echo json_encode($activity);
    $response->send();
});
$klein->respond('GET', '/api/tags/[i:steamID]/[i:depID]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    $member = $department->GetMember($request->steamID);

    $response->header("Content-Type", "application/json");

    if (!$member) {
        echo json_encode(['error' => 'No member found with that SteamID.']);
        $response->send();
        die();
    }

    $tags = $member->GetTags();
    $tagList = [];
    foreach($tags as $tagKey => $tag) {
        if (!$tag->exists) continue;

        array_push($tagList, ["name" => $tag->GetName(), "slug" => $tag->GetSlug(), "id" => $tag->GetID(), "color" => $tag->GetColor(), "expires" => ($tag->Expires() ? $tag->GetExpireTime() : false)]);
    }


    echo json_encode($tagList);
    $response->send();
});
$klein->respond('GET', '/api/points/[i:steamID]/[i:depID]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    $member = $department->GetMember($request->steamID);

    $response->header("Content-Type", "application/json");

    if (!$member) {
        echo json_encode(['error' => 'No member found with that SteamID.']);
        $response->send();
        die();
    }

    $points = $member->GetPoints();
    $pointsList = [];
    foreach($points as $pointsKey => $point) {
        if (!$point->exists) continue;

        $comments = GetCommentsForPoints($point->GetID());
        $data = [
            "id" => $point->GetID(),
            "user" => [
                "name" => $point->GetMember()->GetName(),
                "steamid64" => $point->GetMember()->GetSteamID64()
            ],
            "officer" => [
                "name" => $point->GetOfficer()->GetName(),
                "steamid64" => $point->GetOfficer()->GetSteamID64()
            ],
            "amount" => $point->GetAmount(),
            "reason" => $point->GetReason(),
            "expires" => ($point->Expires() ? $point->GetExpireTime() : false),
            "created" => $point->GetCreated()
        ];

        foreach($comments as $commentKey => $comment) {
            $user = new User($comment['userid']);
            $comments[$commentKey]['user'] =$user->GetDataAsArray();
        }
        $data['comments'] = $comments;


        array_push($pointsList, $data);
    }


    echo json_encode($pointsList);
    $response->send();
});

$klein->respond('GET', '/api/calendar/event/[i:eventID]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $meeting = new Meeting($request->eventID);

    if (!$meeting->exists) {
        $response->code(404);
        $response->send();
        die();
    }

    $response->header("Content-Type", "application/json");

    $payload = [
        'id' => $meeting->GetID(),
        'title' => $meeting->GetTitle(),
        'department' => $meeting->GetDepartment()->GetName(),
        'date' => $meeting->GetTime(),
        'mandatory' => $meeting->IsMandatory(),
        'created' => $meeting->GetCreated(),
        'creator' => [
            'id' => $meeting->GetCreator()->GetSteamID64(),
            'name' => $meeting->GetCreator()->GetName(),
            'avatar' => $meeting->GetCreator()->GetAvatarURL(),
        ],

    ];

    echo json_encode($payload);
    $response->send();
});