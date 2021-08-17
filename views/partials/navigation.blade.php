
@if($me->exists)
    <a class="item">
        <img class="ui mini right spaced circular image" src="{{ $me->GetAvatarURL() }}">
        Hello, {{ $me->GetName() }}
    </a>
    <a class="item" href="/">
        <i class="home icon"></i>
        Home
    </a>
    @if($me->IsAdmin())
        <a class="item" href="/admin">
            <i class="hammer icon"></i>
            Admin
        </a>
        <a class="item" href="/audit">
            <i class="history icon"></i>
            Audit Logs
        </a>
    @endif
    @if($me->IsHigherUp())
        <a class="item" href="/department">
            <i class="users cog icon"></i>
            Manage
        </a>
    @endif
@else
    <a class="item" href="{{ $steam->loginUrl() }}">
        <img src="/public/assets/login.png" class="center">
    </a>
@endif

<a class="item">
</a>

<a class="item" href="/roster">
    <i class="table icon"></i>
    Roster
</a>
@if($me->exists)
<a class="item" href="/docs">
    <i class="file outline icon"></i>
    Documents
</a>
<a class="item" href="/calendar">
    <i class="calendar outline icon"></i>
    Calendar
</a>
<a class="item" href="/forms">
    <i class="clipboard outline outline icon"></i>
    Forms
</a>
<a class="disabled item">
    Lookup
</a>
@endif