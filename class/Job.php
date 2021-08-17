<?php

require_once("resource/jobs.php");

class Job
{
    public $name;
    public $job;
    public $category;
    public $departmentID;
    public $uniqueID;
    public $higherUp;
    public $exists;

    function __construct($job = null)
    {
        $this->exists = false;
        if (!$job) return false;

        $jobData = GetJob($job);
        if (!$jobData) return;

        $this->name = $jobData['name'];
        $this->job = $jobData['job'];
        $this->category = $jobData['category'];

        $this->exists = true;
    }
    // Get methods
    public function GetName() {
        return $this->name;
    }
    public function GetClass() {
        return $this->job;
    }
    public function GetDepartmentID() {
        return isset($this->departmentID) ? $this->departmentID : false;
    }
    public function GetUniqueID() {
        return isset($this->uniqueID) ? $this->uniqueID : false;
    }
    public function GetMembers() {
        if (!$this->exists) return false;
        $membersData = GetJobMembers($this->job);
        if (!$membersData) return [];
        $members = [];
        foreach ($membersData as $memberData) {
            $member = new Member($memberData['userid'], $this->job, $this->departmentID);

            array_push($members, $member);
        }

        return $members;
    }
    public function IsHigherUp() {
        return $this->higherUp;
    }
    public function HasMember($id) {
        foreach($this->GetMembers() as $member) {
            if ($member->GetSteamID64() == $id) {
                return true;
            }
        }

        return false;
    }
    // More job wide get methods
    public function GetAll() {
        $jobsData = GetAllJobs();
        if (!$jobsData) return [];

        $jobs = [];

        // This is a little hacky, but it's ight. It prevents every job being manually checked in the database again, so I think it's worth it.
        foreach ($jobsData as $jobData) {
            $job = new Job();
            $job->name = $jobData['name'];
            $job->job = $jobData['job'];
            $job->category = $jobData['category'];

            $this->exists = true;

            array_push($jobs, $job);
        }

        return $jobs;
    }
}