<?php
// Auth stuff
$klein->respond('/login', function ($request, $response, $service) {
    global $steam;

    $user = new User($steam->steamid);
    if (!$user->exists) {
        $user = $user->Create($steam->steamid, $steam->personaname, $steam->avatarfull);
    } elseif ($user->GetBanned()) {
        $user->ClearSessions();


        $response->redirect("/banned", 200);
        $response->send();
        die();
    }

    $sessionKey = $user->CreateSessionKey();
    setcookie('session_key', $sessionKey, time() + (60*60*24*30));

    $service->startSession();
    $response->redirect("/", 200);
});