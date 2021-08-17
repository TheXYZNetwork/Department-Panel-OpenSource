<button id="create_event_button" class="ui right floated inverted blue button">Create Event</button>

<div id="create_event_modal" class="ui modal">
    <div class="header">
        Create an event
    </div>
    <div class="content">
        <form id="create_event_form" action="/calendar/event/create" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Name</label>
                <input placeholder="Weekly Meeting" name="name" type="text">
            </div>
            <div class="field">
                <label>Department</label>
                <select name="department" class="ui search dropdown">
                    <option value="">Select Department</option>
                    <?php $deps = new Department(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($deps->GetAll() as $dep)
                        @if(!$dep->IsHigherUp($me->GetSteamID64()))
                            @continue
                        @endif
                        <option value="{{ $dep->GetID() }}">{{ $dep->GetName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="two fields">
                <div class="field">
                    <div class="ui toggle checkbox">
                        <input type="checkbox" tabindex="0" class="hidden" name="mandatory">
                        <label>Mandatory</label>
                    </div>
                </div>
                <div class="field">
                    <label>Date & Time</label>
                    <input type="hidden" name="calendar" id="create_event_calendar_shadow">
                    <div class="ui calendar" id="create_event_calendar">
                        <div class="ui input left icon">
                            <i class="calendar icon"></i>
                            <input type="text" placeholder="Date & Time" autocomplete="off">
                        </div>
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
        <button class="ui green right labeled icon button" type="submit" form="create_event_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#create_event_button").click(function(){
            $("#create_event_modal").modal('show');

            var today = new Date();
            $('#create_event_calendar')
                .calendar({
                    minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 1),
                    maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() + 30),

                    minTimeGap: 30,

                    onChange: function(date) {
                        let meetingTimestamp = date.getTime()/1000;

                        $('#create_event_calendar_shadow').val(meetingTimestamp);
                    }
                })
            ;
        });
        $('.ui.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('#create_event_form')
            .form({
                fields: {
                    name: {
                        identifier: 'name',
                        rules: [
                            {
                                type   : 'minLength[2]',
                                prompt : 'The name must be at least 2 characters'
                            },
                            {
                                type   : 'maxLength[32]',
                                prompt : 'The name cannot be longer than 32 characters'
                            }
                        ]
                    },
                    department: {
                        identifier: 'department',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : 'Please select a department'
                            }
                        ]
                    }
                }
            })
        ;
    });
</script>