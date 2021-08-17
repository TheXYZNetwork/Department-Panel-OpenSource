@extends('layouts.app')

@section('title', 'Manage')

<?php
$department = new Department($rosterID);
?>

@section('content')
    <h1 class="ui header">Manage - {{ $department->GetName() }}</h1>

    <h2 class="ui header">Tags</h2>
    <div class="ui grid">
        <div class="four wide column">
            @include('partials.department.tagcreate')
        </div>
        <div class="four wide column">
            @include('partials.department.tagdelete')
        </div>
    </div>

    <h2 class="ui header">Calendar</h2>
    <div class="ui grid">
        <div class="four wide column">
            @include('partials.department.calendardelete')
        </div>
    </div>

    <h2 class="ui header">Announcements</h2>
    <div class="ui grid">
        <div class="four wide column">
            @include('partials.department.announcementcreate')
        </div>
        <div class="four wide column">
            @include('partials.department.announcementdelete')
        </div>
    </div>
@endsection