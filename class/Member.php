<?php

require_once("resource/members.php");
require_once("resource/tags.php");
require_once("resource/points.php");

class Member
{
    public $name;
    public $steamID;
    public $job;
    public $department;
    public $activity;
    public $logs;
    public $tags;
    public $points;

    function __construct($steam64 = null, $job = null, $depID = null)
    {
        $this->exists = false;
        if (!$steam64) return false;
        if (!$job) return false;

        $this->steamID = $steam64;
        $this->job = $job;

        if ($depID) {
            $this->department = $depID;
        }

        $this->exists = true;
    }
    // Get methods
    public function GetName() {
        if ($this->name) {
            return $this->name;
        }

        $memberData = GetMemberInfo($this->GetSteamID64());
        if (!$memberData) return "Unknown";

        $this->name = $memberData['rpname'];
        return $this->name;
    }
    public function GetJob() {
        return new Job($this->job);
    }
    public function GetJobClass() {
        return $this->job;
    }
    public function GetSteamID64() {
        return $this->steamID;
    }
    public function GetDepartment() {
        if (!$this->department) return false;

        return new Department($this->department);
    }
    public function GetActivity() {
        if ($this->activity) {
            return $this->activity;
        }

        $activityData = GetMemberActivity($this->GetSteamID64(), $this->GetDepartment()->GetIdentifier());
        if (!$activityData) return [["join" => 0, "leave" => 0]];

        $this->activity = $activityData;

        return $activityData;
    }
    public function GetRecentActivity() {
        $recentActivity = [];

        foreach ($this->GetActivity() as $activity) {
            if ($activity['join'] < (time() - (60*60*24*7))) continue;

            array_push($recentActivity, $activity);
        }
        return $recentActivity;
    }
    public function GetMostRecentActivity() {
        return $this->GetActivity()[0];
    }
    public function GetRecentPlaytime()
    {
        $recent = $this->GetRecentActivity();
        $total = 0;

        foreach ($recent as $activity) {
            $total += ($activity['leave'] - (isset($activity['join']) ? $activity['join'] : time()));
        }

        return $total;
    }
    public function GetLogs() {
        if ($this->logs) {
            return $this->logs;
        }

        $activityData = GetMemberLogs($this->GetSteamID64(), $this->GetDepartment()->GetIdentifier());
        if (!$activityData) return [["join" => 0, "leave" => 0]];

        $this->logs = $activityData;

        return $activityData;
    }

    public function GetTags() {
        if ($this->tags) {
            return $this->tags;
        }

        $allTags = GetMembersTags($this->GetSteamID64(), $this->GetDepartment()->GetID());
        if (!$allTags) return [];

        $myTags = [];

        foreach($allTags as $tagData) {
            $tag = new Tag($tagData['tag_id']);
            if (!$tag->exists) continue;

            if ($tag->Expires()) {
                $tag->expireTime = $tagData['expires'];
            }

            array_push($myTags, $tag);
        }

        $this->tags = $myTags;

        return $myTags;
    }
    public function GiveTag($tag, $expire) {
        if (!$this->exists) return;
        if (!$tag->exists) return;

        GiveMemberTag($this->GetSteamID64(), $tag->GetID(), $this->GetDepartment()->GetID(), $expire);
    }
    public function RemoveTag($tag) {
        if (!$this->exists) return;
        if (!$tag->exists) return;

        RemoveMemberTag($this->GetSteamID64(), $tag->GetID(), $this->GetDepartment()->GetID());
    }
    public function HasTag($tag) {
        if (!$this->exists) return;
        if (!$tag->exists) return;

        foreach($this->GetTags() as $myTag) {
            if ($myTag->GetID() == $tag->GetID()) return true;
        }

        return false;
    }

    public function GetPoints() {
        if (!$this->exists) return;

        if ($this->points) {
            return $this->points;
        }

        $allPoints = GetMemberPoints($this->GetSteamID64(), $this->GetDepartment()->GetID());
        if (!$allPoints) return [];

        $myPoints = [];

        foreach($allPoints as $pointsData) {
            $points = new Points($pointsData['id']);
            if (!$points->exists) continue;

            array_push($myPoints, $points);
        }

        $this->points = $myPoints;

        return $myPoints;
    }
    public function GivePoints($department, $amount, $reason, $expires, $officer) {
        if (!$this->exists) return;
        if (!$officer->exists) return;
        if (!$department->exists) return;

        GiveMemberPoints($this->GetSteamID64(), $department->GetID(), $officer->GetSteamID64(), $amount, $reason, $expires);
    }
    public function GetPointsTotal() {
        $points = $this->GetPoints();
        $total = 0;

        foreach($points as $point) {
            if ($point->Expires() and ($point->GetExpireTime() < time())) continue;

            $total += $point->GetAmount();
        }

        return $total;
    }
}