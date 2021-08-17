@extends('layouts.app')

@section('title', 'Roster')

@section('content')
    <h1 class="ui header">Rosters</h1>

    <?php $department = new Department; ?>

    <div class="ui grid">
    @foreach($department->GetAll() as $dep)
        <div class="four wide column">
            <a class="ui inverted segment" href="/roster/{{ $dep->GetID() }}" style="text-align: left; display: block;">
                <h3 class="ui header">{{ $dep->GetName() }}</h3>
                {{ number_format($dep->GetTotalMembers()) }} members, across {{ number_format($dep->GetTotalJobs()) }} jobs.
            </a>
        </div>
    @endforeach
    </div>
@endsection