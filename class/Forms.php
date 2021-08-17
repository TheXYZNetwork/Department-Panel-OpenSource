<?php

require_once("resource/form.php");

class Form
{
    public $id;
    public $depID;
    public $title;
    public $desc;
    public $viewability;
    public $responseViewability;
    public $responses;
    public $elements;
    public $creator;
    public $published;
    public $deleted;
    public $created;
    public $modified;
    public $exists;

    function __construct($id = null)
    {
        $this->exists = false;

        if (!$id) return;

        $this->id = $id;

        $formData = GetForm($id);
        if (!$formData) return;

        $this->depID = $formData['department_id'];
        $this->title = $formData['title'];
        $this->desc = $formData['description'];
        $this->creator = $formData['userid'];
        $this->viewability = $formData['viewability'];
        $this->responseViewability = $formData['viewability_responses'] or "[]";
        $this->published = $formData['published'] && $formData['published'] == 1;
        $this->deleted = $formData['deleted'] && $formData['deleted'] == 1;
        $this->created = $formData['created'];
        $this->modified = $formData['modified'];

        $this->exists = true;
    }

    public function Create($depID, $title, $desc, $viewability, $responseViewability, $creator)
    {
        $id = CreateForm($depID, $title, $desc, $viewability, $responseViewability, $creator);

        return new Form($id);
    }

    // Basis get functions
    public function GetID() {
        if (!$this->exists) return;

        return $this->id;
    }
    public function GetTitle() {
        if (!$this->exists) return;

        return $this->title;
    }
    public function GetDescription() {
        if (!$this->exists) return;

        return $this->desc;
    }
    public function GetDepartmentID() {
        if (!$this->exists) return;

        return $this->depID;
    }
    public function GetDepartment() {
        if (!$this->exists) return;

        return new Department($this->GetDepartmentID());
    }
    public function GetCreator()
    {
        if (!$this->exists) return false;

        return new User($this->creator);
    }
    public function GetViewability() {
        if (!$this->exists) return false;

        return json_decode($this->viewability, true);
    }
    public function GetResponseViewability() {
        if (!$this->exists) return false;

        return json_decode($this->responseViewability, true);
    }
    public function GetElements() {
        if (!$this->exists) return;
        if ($this->elements) return $this->elements;

        $elements = GetFormElements($this->GetID());

        if (!$elements) return [];

        foreach($elements as $key => $element) {
            if($elements[$key]['data'] and (!$elements[$key]['data'] == "")) {
                $elements[$key]['data'] = json_decode($elements[$key]['data'], true) or [];
            }
        }

        $this->elements = $elements;

        return $elements;
    }
    public function GetResponses() {
        if (!$this->exists) return;
        if ($this->responses) return $this->responses;

        $responses = GetAllResponsesForForm($this->GetID());

        if (!$responses) return [];

        $responseData = [];
        foreach($responses as $response) {
            $response = new Response($response['id']);
            if (!$response->exists) continue;

            array_push($responseData, $response);
        }

        return $responseData;
    }
    public function IsPublished()
    {
        if (!$this->exists) return false;

        return $this->published;
    }
    // Set Elements
    public function SetTitle($title, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetTitle() == $title) return;

        UpdateFormTitle($this->GetID(), $userID, $title);
    }
    public function SetDescription($desc, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetDescription() == $desc) return;

        UpdateFormDesc($this->GetID(), $userID, $desc);
    }
    public function SetViewability($viewability, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetViewability() == $viewability) return;

        UpdateFormViewability($this->GetID(), $userID, $viewability);
    }
    public function SetResponseViewability($responseViewability, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetResponseViewability() == $responseViewability) return;

        UpdateFormResponseViewability($this->GetID(), $userID, $responseViewability);
    }
    public function SetPublished($publish, $userID)
    {
        if (!$this->exists) return false;

        if ($this->IsPublished() == $publish) return;

        UpdateFormPublished($this->GetID(), $userID, $publish);
    }
    public function SetDeleted($deleted, $userID)
    {
        if (!$this->exists) return false;

        UpdateFormDeleted($this->GetID(), $userID, $deleted, $this->GetDepartmentID());
    }
    // Actions
    public function AddElement($type, $title, $sorting, $data = null) {
        if (!$this->exists) return;

        AddFormElement($this->GetID(), $type, $title, $sorting, $data);
    }
    public function ClearElements() {
        if (!$this->exists) return;

        ClearFormElements($this->GetID());
    }
    public function CreateResponse($id64) {
        if (!$this->exists) return;

        $response = new Response();

        return $response->Create($this->GetID(), $id64);
    }

    public function CanView($userID = null) {
        if (!$this->exists) return false;

        if (!$userID) {
            $userID = "";
        }

        $user = new User($userID);
        if ($user->exists and $user->IsAdmin()) {
            return true;
        }

        if ($this->GetCreator()->GetSteamID64() == $userID) return true;

        foreach($this->GetViewability() as $accessID) {
            if (!$accessID) continue;
            if ($accessID ==  "*") return true;
            if ($accessID ==  "$") { // Anyone in the department can see it
                if ($this->GetDepartment()->GetMember($userID)) {
                    return true;
                }
            };

            $accessType = $accessID[0];
            $realAccessID = substr($accessID, 1);
            if ($accessType == "!") {
                $job = new Job($realAccessID);

                if ($job->HasMember($userID)) return true;
            } elseif($accessType == "#") {
                $tag = new Tag($realAccessID);
                $member = $this->GetDepartment()->GetMember($userID);

                if (!$member or !$member->exists) continue;
                if ($member->HasTag($tag)) return true;
            }
        }

        return false;
    }

    public function CanViewResponses($userID = null) {
        if (!$this->exists) return false;

        if (!$userID) {
            $userID = "";
        }

        $user = new User($userID);
        if ($user->exists and $user->IsAdmin()) {
            return true;
        }

        if ($this->GetCreator()->GetSteamID64() == $userID) return true;

        foreach($this->GetResponseViewability() as $accessID) {
            if (!$accessID) continue;
            if ($accessID ==  "$") { // Anyone in the department can see it
                if ($this->GetDepartment()->GetMember($userID)) {
                    return true;
                }
            };

            $accessType = $accessID[0];
            $realAccessID = substr($accessID, 1);
            if ($accessType == "!") {
                $job = new Job($realAccessID);

                if ($job->HasMember($userID)) return true;
            } elseif($accessType == "#") {
                $tag = new Tag($realAccessID);
                $member = $this->GetDepartment()->GetMember($userID);

                if (!$member or !$member->exists) continue;
                if ($member->HasTag($tag)) return true;
            }
        }

        return false;
    }

    // General class usage
    public function GetDepartmentForms($depID)
    {
        $depsForms = GetAllFormsForDepartment($depID);
        if (!$depsForms) return [];

        $allForms = [];
        foreach ($depsForms as $formData) {
            $form = new Form($formData['id']);
            if (!$form->exists) continue;

            array_push($allForms, $form);
        }

        return $allForms;
    }
}