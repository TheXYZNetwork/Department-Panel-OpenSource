@extends('layouts.app')

@section('title', 'Documents')

<?php
$department = new Department($depID);
?>

@section('content')
    <h1 class="ui header">Documents - {{ $department->GetName() }}</h1>

    <h2 class="ui header">Choose a document to review.</h2>

    <div class="ui three column grid">
        @foreach($department->GetDocuments() as $doc)
            @if (!$doc->CanView($me->GetSteamID64()))
                @continue
            @endif
            @if(!$doc->IsPublished() and !$department->IsHigherUp($me->GetSteamID64()))
                @continue
            @endif
            <div class="column">
                @include('partials.doc.thumbnail', ['doc' => $doc])
            </div>
        @endforeach
        @if($department->IsHigherUp($me->GetSteamID64()))
        <div class="column">
            @include('partials.doc.create')
        </div>
        @endif
    </div>
@endsection