@extends('layouts.app')

@section('title', 'Calendar')

@section('content')
    <h1 class="ui header">Calendar</h1>

        <div class="ui inverted segment">
                <div class="ui grid">
                        <div class="five wide column">
                            <form id="calendar_search" class="ui form" method="GET" >
                                <select id="filter_department" name="dep" class="ui fluid search dropdown">
                                    <option value="">Show event for a specific Department</option>
                                    @foreach ((new Department())->GetAll() as $dep)
                                        <option value="{{ $dep->GetID() }}">{{ $dep->GetName() }}</option>
                                    @endforeach
                                </select>
                            </form>
                        </div>
                    <div class="two wide column">
                        <button form="calendar_search" type="submit" class="ui fluid submit button">Filter</button>
                    </div>
                    <div class="one wide column">
                        <button type="submit" class="ui fluid submit red button" onclick="window.location = window.location.href.split('?')[0];">Clear</button>
                    </div>
                    <div class="eight wide column">
                        @if($me->IsHigherUp())
                            @include('partials.calendar.actions.create')
                        @endif
                    </div>
                </div>
        </div>

    <div class="ui segment">
        <div id='calendar'></div>
    </div>

    <div class="ui modal" id="modal_event">
        <div class="header">Meeting</div>
        <div class="scrolling content" id="modal_event_content">
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendar');
            var calendar = new FullCalendar.Calendar(calendarEl, {
                    initialView: 'dayGridMonth',
                    events: [

                        @foreach((new Meeting)->GetAll(isset($_GET['dep']) ? $_GET['dep'] : false) as $meeting)
                            <?php $col = $config->get('Calendar')[array_rand($config->get('Calendar'))]; ?>
                        {
                            id: '{{ $meeting->GetID() }}',
                            title: '{{ strtoupper($meeting->GetDepartment()->GetIdentifier()) }} - {{ $meeting->GetTitle() }}',
                            timeZone: 'local',
                            start: {{ $meeting->GetTimeMilSec() }},
                            end: {{ $meeting->GetTimeMilSec() }} + (60*30*1000),
                            display: 'block',
                            backgroundColor: '{{ $col }}',
                            borderColor: '{{ $col }}'
                        },
                        @endforeach
                    ],
                    eventClick: async function(info) {
                        console.log(info.event);
                        $('#modal_event').modal('show');

                        let contents = $('#modal_event_content');
                        contents.empty();

                        contents.html(`
                            <div class="ui active inverted dimmer">
                                <div class="ui large text loader">Loading Activity</div>
                            </div>
                        `)

                        let eventData = await GetEventData(info.event.id);
                        contents.empty();

                        if (eventData.error) {
                            contents.html(`
                                <div class="ui negative message">
                                    <div class="header">
                                        An error has occurred!
                                    </div>
                                    <p>${eventData.error}</p>
                                </div>
                            `)
                            return;
                        }

                        contents.append(`
<h1>${ eventData.title }</h1>

<div class="ui inverted segment">
    ${ eventData.mandatory ? `<div class="right aligned floating ui teal label">Mandatory</div>` : "" }
    <div class="ui grid">
        <div class="four wide column">
            <h4 class="ui inverted header">Host
                <div class="sub header">${ eventData.creator.name }</div>
            </h4>
        </div>
        <div class="five wide column">
            <h4 class="ui inverted header">Department
                <div class="sub header">${ eventData.department }</div>
            </h4>
        </div>
        <div class="three wide column">
            <h4 class="ui inverted header">When
                <div class="sub header">${ timeago.format(parseInt(eventData.date) * 1000) }</div>
            </h4>
        </div>
        <div class="four wide column">
            <h4 class="ui inverted header">Created
                <div class="sub header">${ timeago.format(parseInt(eventData.created) * 1000) }</div>
            </h4>
        </div>
    </div>
</div>
                        `)

                    }
            });
            calendar.render();
        });

        async function GetEventData(eventID) {
            return $.ajax({
                url: `/api/calendar/event/${eventID}`,
                type: "GET"
            });
        }

        @if (isset($_GET['dep']))
            $('#filter_department').dropdown('set selected', {{  $_GET['dep'] }});
        @endif
    </script>
@endsection