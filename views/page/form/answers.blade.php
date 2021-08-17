@extends('layouts.app')

@section('title', 'Forms')

<?php
$response = new Response($responseID);
$form = new Form($formID);
$department = $form->GetDepartment();
?>

@section('content')
    <h1 class="ui header">
        {{ $form->GetTitle() }} - Responses
        <div class="sub header">{{ $form->GetDescription() }}</div>
    </h1>

    @if($response->IsArchived())
        <div class="ui warning message">
            <i class="close icon"></i>
            <div class="header">
                This response is archived!
            </div>
            This means that someone has likely already reviewed this response. If you think this was a mistake, you can unarchive this response at the bottom of the page.
        </div>
    @endif

    <h2 class="ui header">
        {{ $response->GetCreator()->GetName() }} ({{ $response->GetCreator()->GetSteamID64() }})
    </h2>

    @foreach($response->GetAnswers() as $answerID => $answer)
        @if(!$answer)
            @continue
        @endif
        <div class="ui inverted segment">
            <h3>{{ $answer['question'] }}</h3>
            @if($answer['type'] == "paragraph")
                <div id="viewer_{{ $answerID }}">
                </div>

                <script>
                    $(document).ready(function() {
                        var quill = new Quill('#viewer_{{ $answerID }}', {});
                        var data = {!! $answer['answer'] !!};
                        quill.setContents(data);
                        quill.enable(false);
                    });
                </script>
            @elseif($answer['type'] == "multichoice")
                <div class="ui list">
                    @foreach(json_decode($answer['answer'], true) as $choice)
                        <div class="item">
                            {{ $choice }}
                        </div>
                    @endforeach
                </div>
            @else
                {{ $answer['answer'] }}
            @endif
        </div>
    @endforeach

    <a href="/forms/responses/{{ $response->GetID() }}/action?t={{ $response->IsArchived() ? "unarchive" : "archive" }}" class="ui right floated orange button">{{ $response->IsArchived() ? "Unarchive" : "Archive" }}</a>
    <button data-steamid="{{ $response->GetCreator()->GetSteamID64() }}" class="ui right floated teal button roster_button_tags">Tags</button>

    @include('partials.roster.actions.tag')

    <script>
        // To close alerts
        $('.message .close')
            .on('click', function() {
                $(this)
                    .closest('.message')
                    .transition('fade')
                ;
            })
        ;
    </script>

@endsection