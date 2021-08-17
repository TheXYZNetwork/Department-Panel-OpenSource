@extends('layouts.app')

@section('title', 'Forms')

<?php
$department = new Department($depID);
?>

@section('content')
    <h1 class="ui header">Forms - {{ $department->GetName() }}</h1>

    <h2 class="ui header">Choose a form to complete.</h2>

    <div class="ui four column grid">
        @foreach($department->GetForms() as $form)
            @if (!$form->CanView($me->GetSteamID64()))
                @continue
            @endif
            @if(!$form->IsPublished() and !$department->IsHigherUp($me->GetSteamID64()))
                @continue
            @endif
            <div class="column">
                @include('partials.form.thumbnail', ['form' => $form])
            </div>
        @endforeach
        @if($department->IsHigherUp($me->GetSteamID64()))
            <div class="column">
                @include('partials.form.create')
            </div>
        @endif
    </div>
@endsection