@extends('layouts.app')

@section('title', 'Forms')

<?php
$form = new Form($formID);
?>

@section('content')
    @if(!$form->IsPublished())
        <div class="ui warning message">
            <i class="close icon"></i>
            <div class="header">
                This form isn't published yet!
            </div>
            In order for others to see and complete your Form, you will need to publish it. You can unpublish it at any time. An unpublished form can only be seen by Department Higher-Ups.
        </div>
    @endif

    <h1 class="ui header">
        {{ $form->GetTitle() }}
        <div class="sub header">{{ $form->GetDescription() }}</div>
    </h1>

    @if($form->CanViewResponses($me->GetSteamID64()))
        <a class="ui right floated yellow button" href="/forms/{{ $form->GetID() }}/edit">Edit</a>
        <a href="/forms/{{ $form->GetID() }}/action?t={{ $form->IsPublished() ? "unpublish" : "publish" }}" class="ui right floated orange button">{{ $form->IsPublished() ? "Unpublish" : "Publish" }}</a>
        <a class="ui right floated purple button" href="/forms/{{ $form->GetID() }}/responses">Responses</a>
        <a href="/forms/{{ $form->GetID() }}/action?t=delete" class="ui red button">Delete</a>
    @endif()
    <a href="/department/{{ $form->GetDepartment()->GetID() }}/forms" class="ui right floated grey button">Back</a>

    <div class="ui inverted segment">
        <form id="form_survey" action="/forms/{{ $form->GetID() }}/complete" method="POST" class="ui inverted form">
            @foreach($form->GetElements() as $elementID => $element)
                    <div class="field">
                        <label>{{ $element['title'] }}</label>
                        @if($element['type'] == "text")
                            <div class="ui fluid input">
                                <input name="{{$elementID}}_answer" type="text" placeholder="Answer to the best of your ability...">
                            </div>
                        @elseif($element['type'] == "dropdown")
                            <div class="ui fluid selection dropdown">
                                <input name="{{$elementID}}_answer" type="hidden">

                                <i class="dropdown icon"></i>
                                <span class="default text">Select Answer</span>
                                <div class="menu">
                                    @foreach((is_array($element['data']) ? $element['data'] : []) as $answerID => $answer)
                                        <div class="item" data-value="{{ $answerID }}">
                                            {{ $answer }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @elseif($element['type'] == "calendar")
                            <div class="ui calendar">
                                <div class="ui input left icon">
                                    <i class="calendar icon"></i>
                                    <input type="text" placeholder="Date/Time" name="{{$elementID}}_answer" autocomplete="off">
                                </div>
                            </div>
                            <script>
                                $('#standard_calendar')
                                    .calendar()
                                ;
                            </script>
                        @elseif($element['type'] == "multichoice")
                            <select name="{{$elementID}}_answer[]"  multiple="" class="ui fluid dropdown">
                                <option value="">Select Answers</option>
                                @foreach((is_array($element['data']) ? $element['data'] : []) as $answerID => $answer)
                                    <option value="{{ $answerID }}">{{ $answer }}</option>
                                @endforeach
                            </select>
                        @elseif($element['type'] == "paragraph")
                            <div class="ui segment">
                                <textarea id="editor_echo_{{ $elementID }}" name="{{ $elementID }}_answer" style="display: none" ></textarea>
                                <div id="editor_{{ $elementID }}"></div>
                            </div>

                            <script>
                                var quill_{{ $elementID }} = new Quill('#editor_{{ $elementID }}', {
                                    modules: {
                                        toolbar: toolbarOptions
                                    },
                                    placeholder: 'Answer to the best of your ability...',
                                    theme: 'snow'  // or 'bubble'
                                });
                                quill_{{ $elementID }}.on('text-change', function(delta, oldDelta, source) {
                                    $('#editor_echo_{{ $elementID }}').val(JSON.stringify(quill_{{ $elementID }}.getContents()));
                                });
                            </script>
                        @endif
                    </div>
            @endforeach

            <div class="ui error message"></div>
            <button type="submit" class="ui fluid primary button">
                Submit Answers
            </button>
        </form>
    </div>

    <script>
        $(document).ready(function() {
            $('.ui.dropdown')
                .dropdown()
            ;

            $('#form_survey')
                .form({
                    fields: {
                        @foreach($form->GetElements() as $elementID => $element)
                        '{{ $elementID }}_answer{{ ($element['type'] == "multichoice") ? "[]" : "" }}' : 'empty',
                        @endforeach
                    }
                })
            ;
        });
    </script>
@endsection