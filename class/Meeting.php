<?php

require_once("resource/meeting.php");

class Meeting
{
    public $id;
    public $depID;
    public $title;
    public $date;
    public $mandatory;
    public $creator;
    public $deleted;
    public $created;
    public $modified;
    public $exists;

    function __construct($id = null)
    {
        $this->exists = false;

        if (!$id) return;

        $this->id = $id;

        $meetingData = GetMeeting($id);
        if (!$meetingData) return;

        $this->depID = $meetingData['department_id'];
        $this->title = $meetingData['title'];
        $this->date = $meetingData['time'];
        $this->mandatory = $meetingData['mandatory'] && $meetingData['mandatory'] == 1;
        $this->creator = $meetingData['userid'];

        $this->deleted = $meetingData['deleted'] && $meetingData['deleted'] == 1;
        $this->created = $meetingData['created'];

        $this->exists = true;
    }

    public function Create($title, $depID, $date, $mandatory, $creator)
    {
        $id = CreateMeeting($title, $depID, $date, $mandatory, $creator);

        return new Meeting($id);
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
    public function GetTime() {
        if (!$this->exists) return;

        return $this->date;
    }
    public function GetTimeMilSec() {
        if (!$this->exists) return;

        return $this->date * 1000;
    }
    public function GetDepartmentID() {
        if (!$this->exists) return;

        return $this->depID;
    }
    public function GetDepartment() {
        if (!$this->exists) return;

        return new Department($this->GetDepartmentID());
    }
    public function GetCreated() {
        if (!$this->exists) return;

        return $this->created;
    }
    public function IsMandatory() {
        if (!$this->exists) return;

        return $this->mandatory;
    }
    public function GetCreator() {
        if (!$this->exists) return;

        return new User($this->creator);
    }

    // Actions
    public function Delete() {
        if (!$this->exists) return;

        DeleteMeeting($this->GetID(), $this->GetDepartmentID());
    }

    // General class usage
    public function GetAll($depID = null) {
        $meetingData = $depID ? GetAllMeetingsForDep($depID): GetAllMeetings();
        if (!$meetingData) return [];

        $meetings = [];

        // This is a little hacky, but it's ight. It prevents every job being manually checked in the database again, so I think it's worth it.
        foreach ($meetingData as $meetingData) {
            $meeting = new Meeting();

            $meeting->id = $meetingData['id'];
            $meeting->depID = $meetingData['department_id'];
            $meeting->title = $meetingData['title'];
            $meeting->date = $meetingData['time'];
            $meeting->mandatory = $meetingData['mandatory'] && $meetingData['mandatory'] == 1;
            $meeting->creator = $meetingData['userid'];

            $meeting->deleted = $meetingData['deleted'] && $meetingData['deleted'] == 1;
            $meeting->created = $meetingData['created'];

            $meeting->exists = true;

            array_push($meetings, $meeting);
        }

        return $meetings;
    }

}