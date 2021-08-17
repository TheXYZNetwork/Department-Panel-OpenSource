@extends('layouts.app')

@section('title', 'Documents')

<?php
$doc = new Document($docID);
?>

@section('content')
    <h1 class="ui header">
        {{ $doc->GetTitle() }}
        <div class="sub header">{{ $doc->GetDescription() }}</div>
    </h1>

    @foreach(array_slice($doc->GetRevisions(), 1) as $revision)
        <div class="ui pink message">
            <div class="header">
                {{ FormatTimeSince("@".$revision['created']) }}
            </div>
            <p>This revision was made by <b>{{ (new User($revision['userid']))->GetName() }}</b> on <b>{{ date("Y-m-d h:i:sa", $revision['created']) }}</b>.</p>
        </div>

        <a href="/docs/{{ $doc->GetID() }}/revision/{{ $revision['id'] }}/reinstate" class="ui top attached violet button" tabindex="0">Reinstate This Revision</a>
        <div class="ui attached segment">
            <div id="viewer-{{ $revision['id'] }}">
            </div>
        </div>

        <script>
            var quill;
            $(document).ready(function() {
                quill = new Quill('#viewer-{{ $revision['id'] }}', {});

                var data = <?= html_entity_decode($revision['revision']) ?>;
                quill.setContents(data);
                quill.enable(false);

            });
        </script>
    @endforeach

@endsection