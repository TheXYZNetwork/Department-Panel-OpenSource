@extends('layouts.app')

@section('title', 'Admin')

@section('content')
    <h1 class="ui header">Admin Panel</h1>

    <h2 class="ui header">Departments</h2>
    <div class="ui grid">
        <div class="four wide column">
            @include('partials.admin.department.register')
        </div>
        <div class="four wide column">
            @include('partials.admin.department.rename')
        </div>
        <div class="four wide column">
            @include('partials.admin.department.order')
        </div>
        <div class="four wide column">
            @include('partials.admin.department.leaders')
        </div>
        <div class="four wide column">
            @include('partials.admin.department.jobs')
        </div>
        <div class="four wide column">
            @include('partials.admin.department.identifier')
        </div>
    </div>

    <h2 class="ui header">Users</h2>
    <div class="ui grid">
        <div class="four wide column">
            @include('partials.admin.user.ban')
        </div>
        <div class="four wide column">
            @include('partials.admin.user.unban')
        </div>
    </div>

    <h2 class="ui header">Other</h2>
    <div class="ui grid">
        <div class="four wide column">
            @include('partials.admin.other.clearcache')
        </div>
    </div>
    
    <script>
        <?php $departments = new Department(); // Would prefer to do this in blade but can't :/ ?>
        let deps = [];
        $(document).ready(function(){
            @foreach ($departments->GetAll() as $dep)
                deps[{{ $dep->GetID() }}] = [];

                @foreach($dep->GetJobs() as $job)
                    deps[{{ $dep->GetID() }}]['{{ $job->GetClass() }}'] = '{{ $job->GetName() }}';
                @endforeach
            @endforeach
        });
    </script>
@endsection