@extends('layouts.app')

@section('title', 'Admin')

@section('content')
    <h1 class="ui header">Audit Logs</h1>

    <div class="ui inverted segment">
        <div class="ui inverted list">
            @foreach(GetRecentAuditLogs() as $audit)
            <?php $user = new User($audit['userid']); ?>
            <div class="item">
                <img class="ui avatar image" src="{{ $user->GetAvatarURL() }}">
                <div class="content">
                    <a class="header">{{ $user->GetName() }} - {{ FormatTimeSince("@".$audit["created"]) }}</a>
                    <div class="description">{{ $audit['log'] }}</div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
@endsection