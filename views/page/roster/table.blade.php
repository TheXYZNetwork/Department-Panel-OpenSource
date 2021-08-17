@extends('layouts.app')

@section('title', 'Roster')

<?php
$department = new Department($rosterID);
$page = isset($_GET['page']) ? $_GET['page'] : 1;

$amHigherUp = false;
if ($me->exists and $department->IsHigherUp($me->GetSteamID64())) {
    $amHigherUp = true;
}

$filteredMembers = $department->GetMembers();
$passedMembers = [];

// Remove any member that doesn't have any of the defined tags
if (isset($_GET['tags'])) {
    // Get members with the tag
    foreach($_GET['tags'] as $tagID) {
        $tag = new Tag($tagID);

        $membersIDs = $tag->GetMembersIDs();

        foreach($filteredMembers as $memberID => $member) {
            if (in_array($member->GetSteamID64(), $membersIDs)) continue;

            unset($filteredMembers[$memberID]);
        }
    }

    // Build a string of existing tags to pre populate the dropdown box
    $existingTags = [];
    foreach($_GET['tags'] as $tagID) {
        //array_push($existingTags, "'" . $tag->GetSlug() . " - " . $tag->GetName() . "'");
        array_push($existingTags, "'$tagID'");
    }
    $existingTags = implode(", ", $existingTags);
}
if (isset($_GET['jobs'])) {
    foreach($filteredMembers as $memberKey => $member) {
        $hasAJob = false;
        if (in_array($member->GetJobClass(), $_GET['jobs'])) {
            $hasAJob = true;
        }

        if (!$hasAJob) {
            unset($filteredMembers[$memberKey]);
        }
    }

    // Build a string of existing jobs to pre populate the dropdown box
    $existingJobs = [];
    foreach($_GET['jobs'] as $job) {
        array_push($existingJobs, "'" . $job . "'");
    }
    $existingJobs = implode(", ", $existingJobs);
}

$existingGet = "";
foreach($_GET as $getPar => $getVar) {
    if ($getPar == "page") continue;

    if (is_array($getVar)) {
        foreach($getVar as $var) {
            $existingGet = $existingGet . "&" . $getPar . "[]=" . $var;
        }
    } else {
        $existingGet = $existingGet . "&" . $getPar . "=" . $getVar;
    }

}
?>

@section('content')
    <h1 class="ui header">
        Rosters - {{ $department->GetName() }}
        <div class="sub header">{{ number_format(count($filteredMembers)) }} Members {{isset($_GET['tags']) || isset($_GET['jobs']) ? "After Filter" :"Total"}}</div>
    </h1>

    <div class="ui inverted segment">
        <form class="ui form" method="GET">
            <div class="ui grid">
                <div class="seven wide column">
                    <label>Tags</label>
                    <select id="filter_tags" name="tags[]" multiple="" class="ui fluid search dropdown">
                        <option value="">Select tags to filter by</option>
                        @foreach ($department->GetTags() as $tag)
                            <option value="{{ $tag->GetID() }}">{{ $tag->GetSlug() }} - {{ $tag->GetName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="seven wide column">
                    <label>Jobs</label>
                    <select id="filter_jobs" name="jobs[]" multiple="" class="ui fluid search dropdown">
                        <option value="">Select jobs to filter by</option>
                        @foreach ($department->GetJobs() as $job)
                            <option value="{{ $job->GetClass() }}">{{ $job->GetName() }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="two wide column">
                    <label>Filter</label>
                    <button type="submit" class="ui fluid submit button">Filter</button>
                </div>
            </div>
        </form>
    </div>

    <table class="ui single line inverted table">
        <thead>
            <tr>
                <th>Role</th>
                <th>Name (SteamID64)</th>
                <th>Recent Activity (h:m:s)</th>
                <th>Last Online</th>
                @if($amHigherUp)
                <th>Points</th>
                @endif
                <th>Tags</th>
                @if($amHigherUp)
                <th>Actions</th>
                @endif
            </tr>
        </thead>
        <tbody>
            @foreach(array_slice($filteredMembers, $config->get('Rows Per Page') * ($page - 1), $config->get('Rows Per Page')) as $member)
            <tr>
                @if($department->GetJobByClass($member->GetJobClass())->IsHigherUp())
                    <td class="left marked orange">
                @else
                    <td>
                @endif
                {{ $member->GetJob()->GetName() }}</td>
                <td>{{ $member->GetName() }} ({{ $member->GetSteamID64() }})</td>
                <td>{{ gmdate("H:i:s", $member->GetRecentPlaytime()) }}</td>
                <td>{{ !($member->GetMostRecentActivity()['join'] == 0) ? FormatTimeSince("@".$member->GetMostRecentActivity()['join']) : "Unknown" }}</td>
                @if($amHigherUp)
                <td>{{ $member->GetPointsTotal() }}</td>
                @endif
                <td>
                    @foreach($member->GetTags() as $tag)
                    <a class="ui {{ $tag->GetColor() }} label" data-tooltip="{{ $tag->GetName() }}">{{ $tag->GetSlug() }}</a>
                    @endforeach
                </td>
                @if($amHigherUp)
                <td>
                    <button data-steamid="{{ $member->GetSteamID64() }}" class="ui inverted purple button roster_button_log">Logs</button>
                    <button data-steamid="{{ $member->GetSteamID64() }}" class="ui inverted yellow button roster_button_activity">Activity</button>
                    <button data-steamid="{{ $member->GetSteamID64() }}" class="ui inverted teal button roster_button_tags">Tags</button>
                    <button data-steamid="{{ $member->GetSteamID64() }}" class="ui inverted red button roster_button_points">Points</button>
                </td>
                @endif
            </tr>
            @endforeach
        </tbody>
    </table>

    @include('partials.roster.actions.logs')
    @include('partials.roster.actions.activity')
    @include('partials.roster.actions.tag')
    @include('partials.roster.actions.points')

    <div class="ui large buttons">
        @if($page > 1)
        <a class="ui secondary icon button" href="?page={{ $page - 1 }}{{ $existingGet }}">
            <i class="left arrow icon"></i>
        </a>
        @endif
        <button class="ui primary button">
            {{ $page }}/{{ ceil(count($filteredMembers)/$config->get('Rows Per Page')) }}
        </button>

        @if(!(ceil(count($filteredMembers)/$config->get('Rows Per Page')) == $page))
        <a class="ui secondary icon button" href="?page={{ $page + 1 }}{{ $existingGet }}">
            <i class="right arrow icon"></i>
        </a>
        @endif
    </div>

    <script>
        $(document).ready(function() {
            $('.activating.element')
                .popup()
            ;
        })
        $('#filter_tags').dropdown('set selected', [<?= $existingTags ?? "" ?>]);
        $('#filter_jobs').dropdown('set selected', [<?= $existingJobs ?? "" ?>]);
    </script>
@endsection