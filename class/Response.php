<?php

require_once("resource/response.php");

class Response
{
    public $id;
    public $formID;
    public $creator;
    public $archived;
    public $created;
    public $exists;

    function __construct($id = null)
    {
        $this->exists = false;

        if (!$id) return;

        $this->id = $id;

        $responseData = GetFormResponse($id);
        if (!$responseData) return;

        $this->formID = $responseData['form_id'];
        $this->creator = $responseData['userid'];
        $this->archived = $responseData['archived'] && $responseData['archived'] == 1;
        $this->created = $responseData['created'];

        $this->exists = true;
    }

    public function Create($formID, $id64)
    {
        $id = CreateFormResponse($formID, $id64);

        return new Response($id);
    }

    public function GiveAnswer($type, $question, $answer) {
        if (!$this->exists) return;

        return CreateFormResponseAnswer($this->GetID(), $type, $question, $answer);
    }

    // Basis get functions
    public function GetID() {
        if (!$this->exists) return;

        return $this->id;
    }
    public function GetForm() {
        if (!$this->exists) return;

        return new Form($this->formID);
    }
    public function GetCreator() {
        if (!$this->exists) return;

        return new User($this->creator);
    }
    public function GetCreated() {
        if (!$this->exists) return;

        return $this->created;
    }
    public function GetAnswers() {
        if (!$this->exists) return [];

        return GetFormResponseAnswers($this->GetID());
    }

    public function IsArchived() {
        if (!$this->exists) return;

        return $this->archived;
    }
    public function SetArchived($archived, $userID)
    {
        if (!$this->exists) return false;

        if ($this->IsArchived() == $archived) return;

        UpdateFormResponseArchived($this->GetID(), $userID, $archived);
    }
}