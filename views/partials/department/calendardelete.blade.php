<div class="ui inverted segment">
    <h2 class="ui header">Delete a Calendar Event</h2>
    <p>Delete a meeting for this department.</p>
    <button id="department_manage_calendar_delete_button" class="fluid ui red button">
        Delete
    </button>
</div>

<div id="department_manage_calendar_delete_modal" class="ui modal">
    <div class="header">
        Delete a Calendar Event
    </div>
    <div class="content">
        <form id="department_manage_calendar_delete_form" action="/department/{{ $department->GetID() }}/calendar/delete" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <div class="ui selection dropdown">
                    <input type="hidden" name="event">
                    <i class="dropdown icon"></i>
                    <div class="default text">Meeting</div>
                    <div class="menu">
                        @foreach((new Meeting)->GetAll($department->GetID()) as $meeting)
                            @if($meeting->GetTime() < time())
                                @continue
                            @endif
                            <div class="item" data-value="{{ $meeting->GetID() }}">
                                {{ $meeting->GetTitle() }} - {{ (date('Y-m-d h:i:s', $meeting->GetTime()))}}
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="department_manage_calendar_delete_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#department_manage_calendar_delete_button").click(function(){
            $("#department_manage_calendar_delete_modal").modal('show');
        });
        $('.ui.checkbox').checkbox();
        $('#department_manage_calendar_delete_form')
            .form({
                fields: {
                    color: {
                        identifier: 'event',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : 'Please select an event'
                            }
                        ]
                    }
                }
            })
        ;
    });
</script>