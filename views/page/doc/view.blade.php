@extends('layouts.app')

@section('title', 'Documents')

<?php
    $doc = new Document($docID);
?>

@section('content')
    @if(!$doc->IsPublished())
        <div class="ui warning message">
            <i class="close icon"></i>
            <div class="header">
                This document isn't published yet!
            </div>
            In order for others to see your Document, you will need to publish it. You can unpublish it at any time. An unpublished document can only be seen by Department Higher-Ups.
        </div>
    @endif

    <h1 class="ui header">
        {{ $doc->GetTitle() }}
        <div class="sub header">{{ $doc->GetDescription() }}</div>
    </h1>


    @if($doc->GetDepartment()->IsHigherUp($me->GetSteamID64()))
        <a href="/docs/{{ $docID }}/edit" class="ui right floated yellow button">Edit</a>
        <a href="/docs/{{ $docID }}/action?t={{ $doc->IsPublished() ? "unpublish" : "publish" }}" class="ui right floated orange button">{{ $doc->IsPublished() ? "Unpublish" : "Publish" }}</a>
        <a href="/docs/{{ $docID }}/revision" class="ui right floated teal button">Revisions</a>
        <a href="/docs/{{ $docID }}/action?t=delete" class="ui red button">Delete</a>
    @endif
    <a href="/department/{{ $doc->GetDepartment()->GetID() }}/docs" class="ui right floated grey button">Back</a>

    <div class="ui segment">
        <div id="viewer">
        </div>
    </div>

    @if($doc->GetInteractability())
    <div class="ui inverted segment">
        <div class="ui inverted comments">
            @foreach($doc->GetComments() as $comment)
                <?php
                    $commenter = new User($comment['userid']);
                ?>
                <div class="comment">
                    <a class="avatar">
                        <img src="{{ $commenter->GetAvatarURL() }}">
                    </a>
                    <div class="content">
                        <a class="author">{{ $commenter->GetName() }}</a>
                        <div class="metadata">
                            <span class="date">{{ FormatTimeSince("@".$comment['created']) }}</span>
                        </div>
                        <div class="text">
                            {{ $comment['comment'] }}
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
        @if($me->exists)
            <form action="/docs/{{ $doc->GetID() }}/comment/add" method="POST" class="ui form">
                <div class="ui fluid action input">
                    <input type="text" placeholder="Add a comment to this document" name="comment">
                    <button class="ui teal button">Post</button>
                </div>
            </form>
        @endif
    </div>
    @endif

    <script>
        var quill;
        var data;
        $(document).ready(function() {
            quill = new Quill('#viewer', {});

            data = <?= $doc->GetContents() ?>;
            quill.setContents(data);
            quill.enable(false);

            // To close alerts
            $('.message .close')
                .on('click', function() {
                    $(this)
                        .closest('.message')
                        .transition('fade')
                    ;
                })
            ;
        });
    </script>

@endsection