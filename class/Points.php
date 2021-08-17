<?php

require_once("resource/points.php");

class Points
{
    public $id;
    public $userID;
    public $depID;
    public $officerID;
    public $amount;
    public $reason;
    public $deleted;
    public $expires;
    public $expireTime;
    public $exists;
    public $created;

    function __construct($id = null)
    {
        $this->exists = false;

        if (!$id) return;

        $this->id = $id;

        $pointData = GetPoints($id);
        if (!$pointData) return;
        $this->userID = $pointData['userid'];
        $this->depID = $pointData['department_id'];
        $this->officerID = $pointData['officer_id'];
        $this->amount = $pointData['amount'];
        $this->reason = $pointData['reason'];
        $this->expires = isset($pointData['expires']);
        $this->expireTime = $this->expires ? $pointData['expires'] : NULL;
        $this->created = $pointData['created'];


        $this->exists = true;
    }

    public function Create($userID, $depID, $amount, $reason)
    {
        $id = CreatePoints($userID, $depID, $amount, $reason);

        return new Points($id);
    }

    // Basis get functions
    public function GetID() {
        if (!$this->exists) return;

        return $this->id;
    }
    public function GetMember() {
        if (!$this->exists) return;

        return $this->GetDepartment()->GetMember($this->userID);
    }
    public function GetOfficer() {
        if (!$this->exists) return;

        return $this->GetDepartment()->GetMember($this->officerID);
    }
    public function GetDepartment() {
        if (!$this->exists) return;

        return new Department($this->depID);
    }
    public function GetAmount() {
        if (!$this->exists) return;

        return $this->amount;
    }
    public function GetReason() {
        if (!$this->exists) return;

        return $this->reason;
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
    public function GetCreated() {
        if (!$this->exists) return;

        return $this->created;
    }

    // Actions
    public function Delete() {
        if (!$this->exists) return;

        DeletePoints($this->GetID(), $this->GetMember()->GetSteamID64(), $this->GetDepartment()->GetID());
    }
}