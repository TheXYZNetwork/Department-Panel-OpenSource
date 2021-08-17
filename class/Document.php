<?php

require_once("resource/document.php");

class Document
{
    public $id;
    public $depID;
    public $title;
    public $desc;
    public $content;
    public $viewability;
    public $interaction;
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

        $docData = GetDocument($id);
        if (!$docData) return;

        $this->depID = $docData['department_id'];
        $this->title = $docData['title'];
        $this->desc = $docData['description'];
        $this->content = $docData['content'];
        $this->viewability = $docData['viewability'];
        $this->interaction = $docData['interaction'] && $docData['interaction'] == 1;
        $this->creator = $docData['userid'];
        $this->published = $docData['published'] && $docData['published'] == 1;
        $this->deleted = $docData['deleted'] && $docData['deleted'] == 1;
        $this->created = $docData['created'];
        $this->modified = $docData['modified'];

        $this->exists = true;
    }

    public function Create($depID, $title, $desc, $content, $viewability, $interaction, $creator)
    {
        $id = CreateDocument($depID, $title, $desc, $content, $viewability, $interaction, $creator);

        return new Document($id);
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
    public function GetContents() {
        if (!$this->exists) return;

        return $this->content;
    }
    public function GetDepartmentID() {
        if (!$this->exists) return;

        return $this->depID;
    }
    public function GetDepartment() {
        if (!$this->exists) return;

        return new Department($this->GetDepartmentID());
    }
    public function GetViewability()
    {
        if (!$this->exists) return false;

        return json_decode($this->viewability, true);
    }
    public function GetCreator()
    {
        if (!$this->exists) return false;

        return new User($this->creator);
    }
    public function GetInteractability()
    {
        if (!$this->exists) return false;

        return $this->interaction;
    }
    public function GetRevisions()
    {
        if (!$this->exists) return false;

        $docRevisions = GetDocumentRevisions($this->GetID());
        if (!$docRevisions) return [];

        return $docRevisions;
    }
    public function GetComments()
    {
        if (!$this->GetInteractability()) return [];

        $docComments = GetDocumentComments($this->GetID());
        if (!$docComments) return [];

        return $docComments;
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

        UpdateDocumentTitle($this->GetID(), $userID, $title);
    }
    public function SetDescription($desc, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetDescription() == $desc) return;

        UpdateDocumentDesc($this->GetID(), $userID, $desc);

    }
    public function SetContents($contents, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetContents() == $contents) return;

        UpdateDocumentContents($this->GetID(), $userID, $contents);
    }
    public function SetViewability($viewability, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetViewability() == $viewability) return;

        UpdateDocumentViewability($this->GetID(), $userID, $viewability);
    }
    public function SetInteractability($interaction, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetInteractability() == $interaction) return;

        UpdateDocumentInteractibility($this->GetID(), $userID, $interaction);
    }
    public function SetPublished($publish, $userID)
    {
        if (!$this->exists) return false;

        if ($this->IsPublished() == $publish) return;

        UpdateDocumentPublished($this->GetID(), $userID, $publish);
    }
    public function SetDeleted($deleted, $userID)
    {
        if (!$this->exists) return false;

        UpdateDocumentDeleted($this->GetID(), $userID, $deleted, $this->GetDepartmentID());
    }
    public function GetRevision($revID)
    {
        if (!$this->exists) return false;

        return GetDocumentRevision($revID);
    }
    public function ReinstateRevision($revID, $userID)
    {
        if (!$this->exists) return false;

        $revision = $this->GetRevision($revID);

        if (!$revision) return false;

        $this->SetContents($revision['revision'], $userID);
    }
    public function AddComment($userID, $comment)
    {
        if (!$this->GetInteractability()) return;

        AddDocumentComment($this->GetID(), $userID, $comment);
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
            if ($accessID ==  "*") return true;
            if ($accessID ==  "$") { // Anyone in the department can see it
                if ($this->GetDepartment()->GetMember($userID)) {
                    return true;
                }
            }

            $accessType = $accessID[0];
            $realAccessID = substr($accessID, 1);
            if ($accessType == "!") {
                $job = $this->GetDepartment()->GetJobByClass($realAccessID);

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
    public function GetDepartmentDocuments($depID)
    {
        $depsDocuments = GetAllDocumentsForDepartment($depID);
        if (!$depsDocuments) return [];

        $allDocs = [];
        foreach ($depsDocuments as $docData) {
            $doc = new Document($docData['id']);
            if (!$doc->exists) continue;

            array_push($allDocs, $doc);
        }

        return $allDocs;
    }
}