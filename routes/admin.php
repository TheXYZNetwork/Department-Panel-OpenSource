<?php
/*
 * VIEWS
 */

$klein->respond('GET', '/admin', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/");
        $response->send();
        die();
    }
    return $blade->make('page.admin.index', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

$klein->respond('GET', '/audit', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/");
        $response->send();
        die();
    }
    return $blade->make('page.admin.audit', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

/*
 * POSTS
 */

$klein->respond('POST', '/admin/department/register', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $department = new Department();
    $department->Create($_POST['name'], $_POST['jobs'], isset($_POST['isgovernment']) ?($_POST['isgovernment'] == "on") : false, !($_POST['identifier'] == "") ? $_POST['identifier'] : false);

    $me->CreateLog("Registered a new department called " . $_POST['name']);

    $response->redirect("/admin", 200);
});

$klein->respond('POST', '/admin/department/rename', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $department = new Department($_POST['department']);

    $me->CreateLog("Renamed the department " . $department->GetName() . " to " . $_POST['name']);

    $department->Rename($_POST['name']);

    $response->redirect("/admin", 200);
});

$klein->respond('POST', '/admin/department/order', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $department = new Department($_POST['department']);

    $order = json_decode($_POST['order']);

    foreach($order as $key => $job) {
        $department->Reorder($job, $key);
    }

    $me->CreateLog("Reordered the jobs for the department " . $department->GetName());

   $response->redirect("/admin", 200);
});

$klein->respond('POST', '/admin/department/higherups', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $department = new Department($_POST['department']);

    $department->ClearHigherUps();
    foreach($_POST['jobs'] as $jobClass) {
        $job = $department->GetJobByClass($jobClass);
        if (!$job) continue;

        $department->AddHigherUp($job);
    }

    $me->CreateLog("Changed the higher-up jobs for the department " . $department->GetName());

    $response->redirect("/admin", 200);
});

$klein->respond('POST', '/admin/department/jobs', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $department = new Department($_POST['department']);
    $jobs = $_POST['jobs'];

    // First yeet all the removed jobs by looping the existing ones and see if they're in the new list
    foreach($department->GetJobs() as $job) {
        if (!in_array($job->GetClass(), $jobs)) {
            $department->RemoveJob($job->GetClass());
        }
    }

    // Now we loop all the jobs and add jobs that don't already exist.
    foreach($jobs as $jobClass) {
        $job = $department->GetJobByClass($jobClass);
        if ($job) continue; // The job already exists

        $department->AddJob($jobClass);
    }

    $me->CreateLog("Changed the jobs for the department " . $department->GetName());

    $response->redirect("/admin", 200);
});

$klein->respond('POST', '/admin/department/identifier', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $department = new Department($_POST['department']);
    $identifier = $_POST['identifier'];

    $me->CreateLog("Changed the identifier for the department " . $department->GetName() . " from " . $department->GetIdentifier() . " to " . $identifier);

    $department->SetIdentifier($identifier, $me->GetSteamID64());


    $response->redirect("/admin", 200);
});

$klein->respond('POST', '/admin/user/ban', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $user = new User($_POST['steamid64']);
    if (!$user->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $user->SetBanned(true);

    $me->CreateLog("Site banned the SteamID64 " . $user->GetSteamID64());

    $response->redirect("/admin", 200);
});

$klein->respond('POST', '/admin/user/unban', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $user = new User($_POST['steamid64']);
    if (!$user->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $user->SetBanned(false);

    $me->CreateLog("Site unbanned the SteamID64 " . $user->GetSteamID64());

    $response->redirect("/admin", 200);
});

$klein->respond('GET', '/admin/cache/clear', function ($request, $response, $service) use ($blade, $me, $steam, $config, $cache) {
    if (!$me->IsAdmin()) {
        $response->redirect("/", 200);
        $response->send();
        die();
    }

    $cache->clear();

    $me->CreateLog("Cleared the cache. It went 🚽 🌊");

    $response->redirect("/admin", 200);
});