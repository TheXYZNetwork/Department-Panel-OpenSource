@extends('layouts.app')

@section('title', 'Forms')

<?php
$form = new Form($formID);
?>

@section('content')
    <h1 class="ui header">
        {{ $form->GetTitle() }} - Responses
        <div class="sub header">{{ $form->GetDescription() }}</div>
    </h1>

    <?php
        $responses = $form->GetResponses();
        $pending = [];
        $archived = [];

        foreach($responses as $response) {
            if ($response->IsArchived()) {
                array_push($archived, $response);
            } else {
                array_push($pending, $response);
            }
        }
    ?>

    <div class="ui top attached tabular menu">
        <div class="item active" data-tab="one">Pending</div>
        <div class="item" data-tab="two">Archived</div>
        <div class="item" data-tab="three">Table</div>
    </div>
    <div class="ui bottom attached tab segment active" data-tab="one">
        @foreach($pending as $response)
            <a class="ui inverted segment" href="/forms/responses/{{ $response->GetID() }}" style="text-align: left; display: block;">
                <h2 class="ui header">
                    {{ $response->GetCreator()->GetName() }}
                    <div class="sub header">{{ FormatTimeSince("@".$response->GetCreated()) }}</div>
                </h2>
            </a>
        @endforeach
    </div>
    <div class="ui bottom attached tab segment" data-tab="two">
        @foreach($archived as $response)
            <a class="ui inverted segment" href="/forms/responses/{{ $response->GetID() }}" style="text-align: left; display: block;">
                <h2 class="ui header">
                    {{ $response->GetCreator()->GetName() }}
                    <div class="sub header">{{ FormatTimeSince("@".$response->GetCreated()) }}</div>
                </h2>
            </a>
        @endforeach
    </div>
    <div class="ui bottom attached tab segment" data-tab="three">
        <div class="ui blue message">This is a test feature, there may be complications and issues.</div>
        <?php $questionOrder = []; ?>
        <table class="ui inverted small celled table tablet stackable">
            <thead>
                <tr>
                    <th>User</th>

                    @foreach($form->GetElements() as $order => $element)
                        <?php array_push($questionOrder, $element['title']) ?>
                        <th>{{ $element['title'] }}</th>
                    @endforeach

                    <th>Archived</th>
                    <th>Submitted</th>
                </tr>
            </thead>
            <tbody>
                @foreach(array_reverse($responses) as $response)
                    <tr>
                        <td>{{ $response->GetCreator()->GetName() }}</td>

                        @foreach($questionOrder as $questionID => $question)
                            <?php
                                $answer = ['type' => "", 'answer' => ""];


                                foreach($response->GetAnswers() as $result) {
                                    if (!($result['question'] == $question)) continue;

                                    $answer = ['type' => $result['type'], 'answer' => $result['answer']];

                                    break;
                                }
                            ?>

                            <td>
                                @if($answer['type'] == "paragraph")
                                    <div id="viewer_{{ $response->GetCreator()->GetSteamID64() }}_{{ $questionID }}">
                                    </div>

                                    <script>
                                        $(document).ready(function() {
                                            var quill = new Quill('#viewer_{{ $response->GetCreator()->GetSteamID64() }}_{{ $questionID }}', {});
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
                            </td>
                        @endforeach

                        <td>{{ $response->IsArchived() ? "Yes" : "No" }}</td>
                        <td>{{ FormatTimeSince("@".$response->GetCreated())}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <script>
        $('.menu .item')
            .tab()
        ;
    </script>
@endsection