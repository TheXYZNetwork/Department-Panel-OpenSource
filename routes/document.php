<?php
/*
 * VIEWS
 */

$klein->respond('GET', '/docs', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.doc.index', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

$klein->respond('GET', '/department/[i:id]/docs', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    $dep = new Department($request->id);
    if (!$dep->exists) {
        $response->redirect("/docs", 404);
        $response->send();
        die();
    }

    return $blade->make('page.doc.dep', ['me' => $me, 'steam' => $steam, 'config' => $config, 'depID' => $request->id])->render();
});

$klein->respond('GET', '/docs/[i:id]/new', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    return $blade->make('page.doc.new', ['me' => $me, 'steam' => $steam, 'config' => $config, 'depID' => $request->id])->render();
});

$klein->respond('GET', '/docs/[i:id]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    $doc = new Document($request->id);
    if (!$doc->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$doc->IsPublished() and !$doc->GetDepartment()->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$doc->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.doc.view', ['me' => $me, 'steam' => $steam, 'config' => $config, 'docID' => $request->id])->render();
});

$klein->respond('GET', '/docs/[i:id]/edit', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    $doc = new Document($request->id);
    if (!$doc->exists) {
        $response->redirect("/docs", 404);
        $response->send();
        die();
    }

    if (!$doc->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$doc->GetDepartment()->IsHigherUp($me->GetSteamID64())) {
        $response->redirect("/docs", 403);
        $response->send();
        die();
    }

    return $blade->make('page.doc.new', ['me' => $me, 'steam' => $steam, 'config' => $config, 'depID' => $doc->GetDepartmentID(), 'docID' => $request->id])->render();
});

$klein->respond('GET', '/docs/[i:id]/revision', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    $doc = new Document($request->id);
    if (!$doc->exists) {
        $response->redirect("/docs", 404);
        $response->send();
        die();
    }


    if (!$doc->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$doc->GetDepartment()->IsHigherUp($me->GetSteamID64())) {
        $response->redirect("/docs", 403);
        $response->send();
        die();
    }

    return $blade->make('page.doc.revision', ['me' => $me, 'steam' => $steam, 'config' => $config, 'depID' => $doc->GetDepartmentID(), 'docID' => $request->id])->render();
});

/*
 * POSTS
 */

$klein->respond('POST', '/docs/[i:depID]/create', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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
    if (strlen($title) > 128) $title = "No title given";

    // Validate description
    $desc = $_POST['desc'];
    if (!$desc) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($desc) < 4) $desc = "No desc given";
    if (strlen($desc) > 500) $desc = "No desc given";

    // Validate description
    $contents = $_POST['contents'];
    if (!$contents) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($contents) < 1) $contents = "{}";

    // Validate viewability
    $viewability = $_POST['viewability'];
    if (!$viewability) {
        $response->code(403);
        $response->send();
        die();
    }

    // Validate interaction
    $interaction = isset($_POST['interaction']) and $_POST['interaction'];
    if ($interaction and ($interaction == "on")) {
        $interaction = true;
    }

    $doc = new Document();
    $doc = $doc->Create($request->depID, $title, $desc, $contents, $viewability, $interaction, $me->GetSteamID64());

    $me->CreateLog("Created a document called " . $title);

    $response->redirect("/docs/" . $doc->GetID(), 200);
});

$klein->respond('POST', '/docs/[i:docID]/edit', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $document = new Document($request->docID);
    if (!$document->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = $document->GetDepartment();

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
    if (strlen($title) < 4) $title = "No reason given";
    if (strlen($title) > 128) $title = "No reason given";

    // Validate description
    $desc = $_POST['desc'];
    if (!$desc) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($desc) < 4) $desc = "No reason given";
    if (strlen($desc) > 500) $desc = "No reason given";

    // Validate description
    $contents = $_POST['contents'];
    if (!$contents) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($contents) < 1) $contents = "{}";

    // Validate viewability
    $viewability = $_POST['viewability'];
    if (!$viewability) {
        $response->code(403);
        $response->send();
        die();
    }

    // Validate interaction
    $interaction = isset($_POST['interaction']) and $_POST['interaction'];
    if ($interaction and ($interaction == "on")) {
        $interaction = true;
    }


    $document->SetTitle($title, $me->GetSteamID64());
    $document->SetDescription($desc, $me->GetSteamID64());
    $document->SetContents($contents, $me->GetSteamID64());
    $document->SetViewability($viewability, $me->GetSteamID64());
    $document->SetInteractability($interaction, $me->GetSteamID64());

    $me->CreateLog("Edited a document called " . $document->GetTitle() . " (" . $document->GetID() . "). This edit may have changed its name.");

    $response->redirect("/docs/" . $document->GetID(), 200);
});

$klein->respond('POST', '/docs/[i:docID]/comment/add', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $document = new Document($request->docID);

    if (!$document->GetInteractability()) {
        $response->code(403);
        $response->send();
        die();
    }

    $comment = $_POST['comment'];
    if (!$comment) {
        $response->code(403);
        $response->send();
        die();
    }
    if ($comment == "") {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($comment) > 1024) {
        $response->code(403);
        $response->send();
        die();
    }

    $document->AddComment($me->GetSteamID64(), $_POST['comment']);

    $me->CreateLog("Added a comment to the document " . $document->GetTitle() . " (" . $document->GetID() . "), the comment was: " . $_POST['comment']);

    $response->redirect("/docs/" . $request->docID, 200);
});

/*
 * GETS
 */

$klein->respond('GET', '/docs/[i:docID]/action', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $document = new Document($request->docID);
    if (!$document->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = $document->GetDepartment();

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    $type = $_GET['t'];

    if (!$type) {
        $response->code(400);
        $response->send();
        die();
    }

    if ($type == "publish") {
        $document->SetPublished(true, $me->GetSteamID64());

        $me->CreateLog("Published the document " . $document->GetTitle() . " (" . $document->GetID() . ").");
    } elseif ($type == "unpublish") {
        $document->SetPublished(false, $me->GetSteamID64());

        $me->CreateLog("Unpublished the document " . $document->GetTitle() . " (" . $document->GetID() . ").");
    } elseif ($type == "delete") {
        $document->SetDeleted(true, $me->GetSteamID64());
        // We do this here because the doc doesn't exist anymore
        $response->redirect("/docs", 200);
        $response->send();
        die();
    } else {
        $response->code(400);
        $response->send();
        die();
    }

    $response->redirect("/docs/" . $document->GetID(), 200);
});

$klein->respond('GET', '/docs/[i:docID]/revision/[i:revisionID]/reinstate', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $document = new Document($request->docID);
    if (!$document->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = $document->GetDepartment();

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    $document->ReinstateRevision($request->revisionID, $me->GetSteamID64());

    $me->CreateLog("Reinstated a version of the document " . $document->GetTitle() . " (" . $document->GetID() . "). The revision ID is " . $request->revisionID);

    $response->redirect("/docs/" . $document->GetID(), 200);
});