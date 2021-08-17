<?php

require_once("resource/tags.php");

class Tag
{
    public $id;
    public $name;
    public $slug;
    public $deleted;
    public $color;
    public $expires;
    public $expireTime;
    public $depID;
    public $modified;
    public $exists;

    function __construct($id = null)
    {
        $this->exists = false;

        if (!$id) return;

        $this->id = $id;

        $tagData = GetTag($id);
        if (!$tagData) return;

        $this->name = $tagData['name'];
        $this->created = $tagData['created'];
        $this->modified = $tagData['modified'];
        $this->slug = $tagData['slug'];
        $this->color = $tagData['color'];
        $this->expires = isset($tagData['expires']);
        $this->depID = $tagData['department_id'];
        $this->deleted = $tagData['deleted'] ? true : false;

        $this->exists = true;
    }

    public function Create($depID, $name, $slug, $color, $expires)
    {
        $id = CreateTag($depID, $name, $slug, $color, $expires);

        return new Tag($id);
    }

    // Basis get functions
    public function GetID() {
        if (!$this->exists) return;

        return $this->id;
    }
    public function GetName() {
        if (!$this->exists) return;

        return $this->name;
    }
    public function GetSlug() {
        if (!$this->exists) return;

        return $this->slug;
    }
    public function GetColor() {
        if (!$this->exists) return;

        return $this->color;
    }
    public function GetDepartmentID() {
        if (!$this->exists) return;

        return $this->depID;
    }
    public function GetDepartment() {
        if (!$this->exists) return;

        return new Department($this->depID);
    }
    public function Expires() {
        if (!$this->exists) return;

        return $this->expires;
    }
    public function GetExpireTime() {
        if (!$this->exists) return;
        if (!$this->expireTime) return;

        return $this->expireTime;
    }
    public function GetMembersIDs() {
        if (!$this->exists) return;

        $membersData = GetTagsMembers($this->GetID());
        $userIDs = [];

        foreach($membersData as $memData) {
            array_push($userIDs, $memData['userid']);
        }

        return $userIDs;
    }

    // Actions
    public function Delete() {
        if (!$this->exists) return;

        // Remove the tag from all existing users
        $members = $this->GetMembersIDs();
        $dep = $this->GetDepartment();
        foreach($members as $memberID) {
            $member = $dep->GetMember($memberID);
            $member->RemoveTag($this);
        }

        DeleteTag($this->GetID());
    }

    // More tag wide get methods
    public function GetAll() {
        $tagsData = GetAllTags();

        $tags = [];
        // This is a little hacky, but it's ight. It prevents every job being manually checked in the database again, so I think it's worth it.
        foreach ($tagsData as $tagData) {
            $tag = new Tag();

            $tag->id = $tagData['id'];
            $tag->name = $tagData['name'];
            $tag->created = $tagData['created'];
            $tag->modified = $tagData['modified'];
            $tag->slug = $tagData['slug'];
            $tag->color = $tagData['color'];
            $tag->expires = isset($tagData['expires']);
            $tag->depID = $tagData['department_id'];
            $tag->deleted = false;

            $tag->exists = true;

            array_push($tags, $tag);
        }

        return $tags;
    }
    public function GetAllForDepartment($depID) {
        $allTags = $this->GetAll();

        $tags = [];
        foreach($allTags as $tag) {
            if(!($tag->GetDepartmentID() == $depID)) continue;

            array_push($tags, $tag);
        }

        return $tags;
    }
}