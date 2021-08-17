<?php

require_once("resource/users.php");
require_once("resource/audit.php");

class User
{
    public $id;
    public $name;
    public $avatarURL;
    public $banned;
    public $joined;
    public $lastSeen;
    public $exists;
    public $isAdmin;

    function __construct($id = null)
    {
        global $config;

        $this->exists = false;
        if (!$id) return;

        $this->id = $id;

        $userData = GetUser($id);
        if (!$userData) return;
        $this->exists = true;

        $this->name = $userData['name'];
        $this->avatarURL = $userData['avatar'];
        $this->banned = $userData['banned'] && $userData['banned'] == 1;
        $this->joined = $userData['joined'];
        $this->lastseen = time();

        $usergroup = GetUserGroup($this->GetSteamID64());
        $this->isAdmin = $usergroup && isset($config->get('Admin Groups')[$usergroup]) && $config->get('Admin Groups')[$usergroup];

    }

    public function Create($id, $name, $avatar)
    {
        CreateUser($id, $name, $avatar);

        return new User($id);
    }

    public function CreateFromSession($key)
    {
        $userID =  GetSessionUser($key);
        if (!$userID) return $this;

        return new User($userID);
    }
    public function CreateSessionKey() {
        $key = GenerateRandomString(32);

        CreateSession($this->id, $key);

        return $key;
    }
    public function ClearSessions() {
        if (!$this->exists) return;

        ClearUserSessions($this->GetSteamID64());
    }
    // Get methods
    public function GetSteamID64() {
        if (!$this->exists) return;

        return $this->id;
    }
    public function GetSteamID32() {
        if (!$this->exists) return;

        return "Unknown";
    }
    public function GetName() {
        if (!$this->exists) return;

        return $this->name;
    }
    public function GetAvatarURL() {
        if (!$this->exists) return;

        return $this->avatarURL;
    }
    public function IsAdmin() {
        if (!$this->exists) return false;

        return $this->isAdmin;
    }
    public function GetDataAsArray() {
        if (!$this->exists) return;

        return ["name" => $this->GetName(), "avatar" => $this->GetAvatarURL()];
    }
    public function GetMembers() {
        if (!$this->exists) return;

        return [];
    }
    public function GetDepartments() {
        if (!$this->exists) return;

        $depsIn = [];
        $allDeps = new Department();

        foreach($allDeps->GetAll() as $dep) {
            if (!$dep->GetMember($this->GetSteamID64())) continue;

            array_push($depsIn, $dep);
        }

        return $depsIn;
    }
    public function IsHigherUpInDep($dep) {
        if (!$this->exists) return;

        return false;
    }
    public function IsHigherUp() {
        if (!$this->exists) return;

        foreach($this->GetDepartments() as $dep) {
            if ($dep->IsHigherUp($this->GetSteamID64())) return true;
        }

        return false;
    }
    public function GetBanned() {
        if (!$this->exists) return;

        return $this->banned;
    }
    public function SetBanned($state) {
        if (!$this->exists) return;

        if ($this->GetBanned() == $state) return;

        if ($state) {
            $this->ClearSessions();
        }

        UpdateUserBan($this->GetSteamID64(), $state);
    }
    public function CreateLog($log) {
        if (!$this->exists) return;

        CreateAuditLog($this->GetSteamID64(), $log);
    }
}