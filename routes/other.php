<?php
/*
 * VIEWS
 */

$klein->respond('GET', '/', function () use ($blade, $me, $steam, $config) {
    return $blade->make('page.index', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

$klein->respond('GET', '/banned', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if ($me->exists and !$me->GetBanned()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }
    return $blade->make('page.banned', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

/*
 * POSTS
 */