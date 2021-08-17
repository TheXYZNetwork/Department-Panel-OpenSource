<?php

require_once("resource/departments.php");

class Department
{
    public $id;
    public $name;
    public $jobs;
    public $identifier;
    public $created;
    public $modified;
    public $exists;

    function __construct($id = null)
    {
        $this->exists = false;

        if (!$id) return;

        $this->id = $id;

        $depData = GetDepartment($id);
        if (!$depData) return;

        $this->name = $depData['name'];
        $this->created = $depData['created'];
        $this->modified = $depData['modified'];
        $this->identifier = isset($depData['identifier']) ? $depData['identifier'] : false;

        $this->exists = true;
    }

    public function Create($name, $jobs, $isGovernment, $identifier)
    {

        $id = CreateDepartment($name, $jobs, $isGovernment, $identifier);

        return new Department($id);
    }

    public function AddJob($jobClass) {
        if (!$this->exists) return false;

        AddJobToDepartment($this->GetID(), $jobClass, 0);
    }
    public function RemoveJob($jobClass) {
        if (!$this->exists) return false;

        $order = 0;
        foreach($this->GetJobs() as $orderID => $job){
            if ($job->GetClass() == $jobClass) {
                $order = $orderID;
                break;
            }
        }

        RemoveJobFromDepartment($this->GetID(), $jobClass, $order);
    }

    public function Rename($newName)
    {
        if (!$this->exists) return false;

        RenameDepartment($this->GetID(), $newName);

        return true;
    }

    public function Reorder($jobClass, $order)
    {
        if (!$this->exists) return false;

        $job = $this->GetJobByClass($jobClass);

        if (!$job) return false;

        ReorderJobInDepartment($job->GetUniqueID(), $order, $this->GetID());

        return true;
    }

    public function AddHigherUp($job)
    {
        if (!$this->exists) return false;
        if (!$job->GetUniqueID()) return false;

        AddDepartmentHigherUps($job->GetUniqueID(), $this->GetID());
    }

    public function ClearHigherUps()
    {
        if (!$this->exists) return false;

        ClearDepartmentHigherUps($this->GetID());
    }

    // Get methods
    public function GetName() {
        if (!$this->exists) return false;

        return $this->name;
    }
    public function GetID() {
        if (!$this->exists) return false;

        return $this->id;
    }
    public function GetMembers() {
        if (!$this->exists) return false;

        $members = [];
        $membersCache = [];

        foreach($this->GetJobs() as $job) {
            if (!$job) continue;
            foreach($job->GetMembers() as $member) {
                if (isset($membersCache[$member->GetSteamID64()])) continue;

                array_push($members, $member);
                $membersCache[$member->GetSteamID64()] = true;
            }
        }

        return $members;
    }
    public function GetMember($id) {
        if (!$this->exists) return false;

        foreach($this->GetJobs() as $job) {
            if (!$job) continue;
            foreach($job->GetMembers() as $member) {
                if ($member->GetSteamID64() == $id) return $member;
            }
        }

        return false;
    }
    public function GetMembersWithTag($tag) {
        if (!$this->exists) return false;

        $tagMemIDs = $tag->GetMembersIDs();
        $users = [];

        foreach($this->GetMembers() as $member) {
            if (!$member) continue;

            if (!in_array($member->GetSteamID64(), $tagMemIDs)) continue;

            array_push($users, $member);
        }

        return $users;
    }
    public function GetJobByClass($class) {
        if (!$this->exists) return false;

        foreach ($this->GetJobs() as $job) {
            if ($job->GetClass() == $class) return $job;
        }

        return false;
    }
    public function GetJobs() {
        if (!$this->exists) return false;

        if ($this->jobs and !empty($this->jobs)) {
            return $this->jobs;
        }

        $jobs = GetDepartmentJobs($this->id);
        $this->jobs = [];
        foreach($jobs as $jobClass) {
            $job = new Job($jobClass['job']);
            if (!$job) continue;
            if (!$job->exists) continue;

            $job->departmentID = $this->GetID();
            $job->uniqueID = $jobClass['id'];
            $job->higherUp = isset($jobClass['higherup']);
            if ($jobClass['higherup']) {
                $job->higherUp = true;
            }

            array_push($this->jobs, $job);
        }

        return $this->jobs;
    }
    public function GetTotalMembers() {
        if (!$this->exists) return false;

        return count($this->GetMembers());
    }
    public function GetTotalJobs() {
        if (!$this->exists) return false;

        return count($this->GetJobs());
    }
    public function GetIdentifier() {
        if (!$this->exists) return false;

        return $this->identifier;
    }
    public function IsHigherUp($id) {
        if (!$this->exists) return false;

        $user = new User($id);
        if ($user->exists and $user->IsAdmin()) {
            return true;
        }

        foreach($this->GetJobs() as $job) {
            if ($job->IsHigherUp() and $job->HasMember($id)) return true;
        }

        return false;
    }
    public function GetTags() {
        if (!$this->exists) return false;

        $baseTag = new Tag();

        return $baseTag->GetAllForDepartment($this->GetID());
    }
    public function GetDocuments() {
        if (!$this->exists) return false;

        $docData = new Document();

        return $docData->GetDepartmentDocuments($this->GetID());
    }
    public function GetForms() {
        if (!$this->exists) return false;

        $form = new Form();

        return $form->GetDepartmentForms($this->GetID());
    }
    public function GetRecentAnnouncements() {
        if (!$this->exists) return false;

        return GetRecentDepartmentAnnouncement($this->GetID());
    }
    public function GetRecentActivity() {
        if (!$this->exists) return 1;
        if (!$this->GetIdentifier()) return 2;

        return GetRecentDepartmentActivity($this->GetIdentifier());
    }
    public function SetIdentifier($identifier = false, $userID)
    {
        if (!$this->exists) return false;

        if ($this->GetIdentifier() == $identifier) return false;

        UpdateDepartmentIdentifier($this->GetID(), $userID, $identifier);
    }
    // Actions
    public function CreateAnnouncement($title, $content, $userID) {
        if (!$this->exists) return false;

        CreateDepartmentAnnouncement($this->GetID(), $title, $content, $userID);
    }
    public function DeleteAnnouncement($announcementID, $userID) {
        if (!$this->exists) return false;

        DeleteDepartmentAnnouncement($this->GetID(), $announcementID, $userID);
    }
    // More job wide get methods
    public function GetAll() {
        $depsData = GetAllDepartments();
        if (!$depsData) return [];

        $deps = [];

        // This is a little hacky, but it's ight. It prevents every job being manually checked in the database again, so I think it's worth it.
        foreach ($depsData as $depData) {
            $dep = new Department();
            $dep->id = $depData['id'];
            $dep->name = $depData['name'];
            $dep->created = $depData['created'];
            $dep->modified = $depData['modified'];
            $dep->identifier = isset($depData['identifier']) ? $depData['identifier'] : false;

            $dep->jobs = $dep->GetJobs();

            $dep->exists = true;

            array_push($deps, $dep);
        }

        return $deps;
    }
}