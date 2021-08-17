<?php
// handler/database.php also needs editing in order to get this to work

$config = [];
// Name of app
$config['App Name'] = "Departments";
// Debug
$config['Debug'] = false;
// Domain
$config['Domain'] = "https://my.website";
// Steam API Key
$config['SteamAPI Key'] = "STEAMAPIKEY";
// How many rows to show on the roster per page
$config['Rows Per Page'] = 20;
// Max days someone can be given a tag that expires (LOA for example)
$config['Tag Expire Max Length'] = 30;

// Usergroups with admin perms
$config['Admin Groups'] = ['superadmin' => true, 'developer' => true];

// The colors used for the events in the Calendar
$config['Calendar'] = ['#B03060', '#FE9A76', '#FFD700', '#32CD32', '#016936', '#008080', '#0E6EB8', '#EE82EE', '#B413EC', '#FF1493', '#A52A2A', '#A0A0A0'];

// Viewability types
$config['Viewability'] = [
    'anyone' => [
        'id' => "anyone",
        'name' => "Anyone",
        'check' => function($userID, $depID) {
            return true;
        }
    ],
    'member' => [
        'id' => "member",
        'name' => "Department Member",
        'check' => function($userID, $depID) {
            $user = new User($userID);
            $dep = new Department($depID);

            if ($dep->GetMember($userID)) {
                return true;
            }

            return false;
        }
    ],
    'higher-up' => [
        'id' => "higher-up",
        'name' => "Department Higher-Up",
        'check' => function($userID, $depID) {
            $user = new User($userID);
            $dep = new Department($depID);

            if ($dep->IsHigherUp($userID)) {
                return true;
            }

            return false;
        }
    ]
];

return $config;