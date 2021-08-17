@extends('layouts.app')

@section('title', 'Home')

@section('content')
    <div class="ui violet message">
        <div class="header">
            This site is currently in a beta stage
        </div>
        There will likely be things broken. Please report any bugs found
    </div>

    <div class="ui grid">
        @if($me->exists)
            <div class="eight wide column">
                <h1 class="ui header">Latest Department Announcements</h1>
                <?php
                // Compile a list of announcements we're allowed to see.
                $allAnnouncements = [];
                foreach($me->GetDepartments() as $department) {
                    $announcements = $department->GetRecentAnnouncements();
                    if (empty($announcements)) continue;

                    foreach($announcements as $announcement) {
                        $announcement['dep'] = $department;
                        array_push($allAnnouncements, $announcement);
                    }
                }

                usort($allAnnouncements, function($a, $b) {
                    return $a['created'] < $b['created'];
                })
                ?>

                @foreach(array_slice($allAnnouncements, 0, 5) as $announcement)
                    <div class="ui inverted segment">
                        <h4 class="ui header">
                            {{ $announcement['title'] }} - {{ FormatTimeSince("@".$announcement['created']) }}
                            <div class="sub header">{{ $announcement['dep']->GetName() }}</div>
                        </h4>
                        <p>
                            {{ $announcement['desc'] }}
                        </p>
                    </div>
                @endforeach
            </div>
            <div class="eight wide column">
                <h1 class="ui header">My Department Recent Activity</h1>
                <table class="ui selectable inverted table">
                    <thead>
                    <tr>
                        <th>Department</th>
                        <th>Recent Playtime</th>
                    </tr>
                    </thead>
                        <tbody>
                        @foreach($me->GetDepartments() as $department)
                            <tr>
                                <td>{{ $department->GetName() }}</td>
                                <td>{{ FormatSeconds($department->GetMember($me->GetSteamID64())->GetRecentPlaytime()) }}</td>
                            </tr>
                        @endforeach
                        </tbody>
                </table>
            </div>
        @endif
        <div class="sixteen wide column">
            <h1 class="ui header">General Department Recent Activity</h1>
            <table class="ui selectable inverted table">
                <thead>
                <tr>
                    <th>Department</th>
                    <th>Recent Playtime</th>
                </tr>
                </thead>
                <tbody>
                <?php
                $allDepartments = (new Department())->GetAll();
                ?>
                @foreach($allDepartments as $department)
                    <tr>
                        <td>{{ $department->GetName() }}</td>
                        <td>{{ FormatSeconds($department->GetRecentActivity()) }}</td>
                    </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection