<?php
/*
 * VIEWS
 */

$klein->respond('GET', '/forms', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.form.index', ['me' => $me, 'steam' => $steam, 'config' => $config])->render();
});

$klein->respond('GET', '/department/[i:id]/forms', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $dep = new Department($request->id);
    if (!$dep->exists) {
        $response->redirect("/docs", 404);
        $response->send();
        die();
    }

    return $blade->make('page.form.dep', ['me' => $me, 'steam' => $steam, 'config' => $config, 'depID' => $request->id])->render();
});

$klein->respond('GET', '/forms/[i:id]/new', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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

    return $blade->make('page.form.new', ['me' => $me, 'steam' => $steam, 'config' => $config, 'depID' => $request->id])->render();
});

$klein->respond('GET', '/forms/[i:id]/edit', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $form = new Form($request->id);
    if (!$form->exists) {
        $response->redirect("/docs", 404);
        $response->send();
        die();
    }

    if (!$form->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }
    if (!$form->CanViewResponses($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$form->GetDepartment()->IsHigherUp($me->GetSteamID64())) {
        $response->redirect("/docs", 403);
        $response->send();
        die();
    }

    return $blade->make('page.form.new', ['me' => $me, 'steam' => $steam, 'config' => $config, 'depID' => $form->GetDepartmentID(), 'formID' => $request->id])->render();
});

$klein->respond('GET', '/forms/[i:id]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $form = new Form($request->id);
    if (!$form->exists) {
        $response->code(403);
        $response->send();
    }

    if (!$form->IsPublished() and !$form->GetDepartment()->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
    }

    if (!$form->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.form.view', ['me' => $me, 'steam' => $steam, 'config' => $config, 'formID' => $request->id])->render();
});

$klein->respond('GET', '/forms/[i:formID]/action', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $form = new Form($request->formID);
    if (!$form->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = $form->GetDepartment();

    if (!$department->IsHigherUp($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }
    if (!$form->CanViewResponses($me->GetSteamID64())) {
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
        $form->SetPublished(true, $me->GetSteamID64());
    } elseif ($type == "unpublish") {
        $form->SetPublished(false, $me->GetSteamID64());
    } elseif ($type == "delete") {
        $form->SetDeleted(true, $me->GetSteamID64());
        // We do this here because the form doesn't exist anymore
        $response->redirect("/forms", 200);
        $response->send();
        die();
    } else {
        $response->code(400);
        $response->send();
        die();
    }

    $response->redirect("/forms/" . $form->GetID(), 200);
});

$klein->respond('GET', '/forms/[i:id]/responses', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $form = new Form($request->id);
    if (!$form->exists) {
        $response->code(403);
        $response->send();
    }

    if (!$form->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }
    if (!$form->CanViewResponses($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.form.responses', ['me' => $me, 'steam' => $steam, 'config' => $config, 'formID' => $request->id])->render();
});

$klein->respond('GET', '/forms/responses/[i:id]', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $response = new Response($request->id);
    $form = $response->GetForm();
    if (!$form->exists) {
        $response->code(403);
        $response->send();
    }

    if (!$form->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }
    if (!$form->CanViewResponses($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    return $blade->make('page.form.answers', ['me' => $me, 'steam' => $steam, 'config' => $config, 'responseID' => $request->id, 'formID' => $form->GetID()])->render();
});


$klein->respond('GET', '/forms/responses/[i:responseID]/action', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $responseData = new Response($request->responseID);
    if (!$responseData->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $form = $responseData->GetForm();
    $department = $form->GetDepartment();

    if (!$form->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }
    if (!$form->CanViewResponses($me->GetSteamID64())) {
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

    if ($type == "archive") {
        $responseData->SetArchived(true, $me->GetSteamID64());
    } elseif ($type == "unarchive") {
        $responseData->SetArchived(false, $me->GetSteamID64());
    } else {
        $response->code(400);
        $response->send();
        die();
    }

    $response->redirect("/forms/responses/" . $responseData->GetID(), 200);
});

/*
 * POSTS
 */


$klein->respond('POST', '/forms/[i:depID]/create', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
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
    if (strlen($desc) > 500) $desc = "No given given";

    // Validate viewability
    $viewability = $_POST['viewability'];
    if (!$viewability) {
        $response->code(403);
        $response->send();
        die();
    }
    // For some reason they're passed as 1 array value? :/
    $viewability = explode(",", $viewability[0]);

    // Validate response viewability
    $responseViewability = $_POST['response_viewability'];
    if (!$responseViewability) {
        $response->code(403);
        $response->send();
        die();
    }
    // For some reason they're passed as 1 array value? :/
    $responseViewability = explode(",", $responseViewability[0]);

    // Validate description
    $elements = $_POST['elements'];
    if (!$elements) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($elements) < 1) $elements = "{}";

    $elements = json_decode($elements, true);


    $form = new Form();
    $form = $form->Create($request->depID, $title, $desc, $viewability, $responseViewability, $me->GetSteamID64());

    foreach($elements as $order => $element) {
        $form->AddElement($element['type'], $element['name'], $order, $element['data'] ?? null);
    }

    $me->CreateLog("Created a form called " . $form->GetTitle() . " (" . $form->GetID() . ").");

    $response->redirect("/forms/" . $form->GetID(), 200);
});

$klein->respond('POST', '/forms/[i:formID]/edit', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $form = new Form($request->formID);
    if (!$form->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $department = $form->GetDepartment();

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

    // Validate viewability
    $viewability = $_POST['viewability'];
    if (!$viewability) {
        $response->code(403);
        $response->send();
        die();
    }
    // For some reason they're passed as 1 array value? :/
    $viewability = explode(",", $viewability[0]);

    // Validate response viewability
    $responseViewability = $_POST['response_viewability'];
    if (!$responseViewability) {
        $response->code(403);
        $response->send();
        die();
    }
    // For some reason they're passed as 1 array value? :/
    $responseViewability = explode(",", $responseViewability[0]);

    // Validate description
    $elements = $_POST['elements'];
    if (!$elements) {
        $response->code(403);
        $response->send();
        die();
    }
    if (strlen($elements) < 1) $elements = "{}";

    $elements = json_decode($elements, true);

    $form->SetTitle($title, $me->GetSteamID64());
    $form->SetDescription($desc, $me->GetSteamID64());
    $form->SetViewability($viewability, $me->GetSteamID64());
    $form->SetResponseViewability($responseViewability, $me->GetSteamID64());
    $form->ClearElements();

    foreach($elements as $order => $element) {
        $form->AddElement($element['type'], $element['name'], $order, $element['data'] ?? null);
    }

    $me->CreateLog("Edited a form called " . $form->GetTitle() . " (" . $form->GetID() . "). This edit may have changed its name.");

    $response->redirect("/forms/" . $form->GetID(), 200);
});

$klein->respond('POST', '/forms/[i:formID]/complete', function ($request, $response, $service) use ($blade, $me, $steam, $config) {
    if (!$me->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    $form = new Form($request->formID);
    if (!$form->exists) {
        $response->code(403);
        $response->send();
        die();
    }

    if (!$form->CanView($me->GetSteamID64())) {
        $response->code(403);
        $response->send();
        die();
    }

    $formattedResponses = [];
    $pureResponses = $_POST;
    foreach($form->GetElements() as $elementID => $element) {
        if (!isset($pureResponses[$elementID . "_answer"])) {
            $response->code(403);
            $response->send();
            die();
        }

        $formattedResponses[$elementID] = $pureResponses[$elementID . "_answer"];

        if ($element['type'] == "dropdown") {
            $formattedResponses[$elementID] = $form->GetElements()[$elementID]['data'][$formattedResponses[$elementID]];
        } elseif ($element['type'] == "multichoice") {
            foreach($formattedResponses[$elementID] as $responseID => $responseData) {
                $formattedResponses[$elementID][$responseID] = $form->GetElements()[$elementID]['data'][$formattedResponses[$elementID][$responseID]];
            }
        }
    }

    $answers = [];
    foreach($form->GetElements() as $elementID => $element) {
        $answers[$elementID] = [
            "type" => $element['type'],
            "question" => $element['title'],
            "answers" => $formattedResponses[$elementID]
        ];
    }

    $formResponse = $form->CreateResponse($me->GetSteamID64());

    foreach($answers as $answer) {
        $formResponse->GiveAnswer($answer['type'], $answer['question'], $answer['answers']);
    }

    $me->CreateLog("Submitted a response for a form called " . $form->GetTitle() . " (" . $form->GetID() . ").");

    $response->redirect("/forms", 200);
});