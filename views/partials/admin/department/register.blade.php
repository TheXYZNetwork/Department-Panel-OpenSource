<div class="ui inverted segment">
    <h2 class="ui header">Register A Department</h2>
    <p>Register a new department and map the jobs that are linked to it.</p>
    <button id="register_department_button" class="fluid ui violet button">
        Register
    </button>
</div>

<div id="register_department_modal" class="ui modal">
    <div class="header">
        Register A Department
    </div>
    <div class="content">
        <form id="register_department_form" action="/admin/department/register" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Name</label>
                <input placeholder="Police Department" name="name" type="text">
            </div>
            <div class="field">
                <label>Jobs</label>
                <select name="jobs[]" multiple="" class="ui search three column dropdown">
                    <option value="">Select Jobs</option>
                    <?php $jobs = new Job(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($jobs->GetAll() as $job)
                        <option value="{{ $job->GetClass() }}">{{ $job->GetName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="two fields">
                <div class="field">
                    <div class="ui toggle checkbox">
                        <input type="checkbox" tabindex="0" class="hidden" name="isgovernment">
                        <label>Is A Government Department</label>
                    </div>
                </div>
                <div class="field">
                    <label>Identifier</label>
                    <input placeholder="pd" name="identifier" type="text">
                </div>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="register_department_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#register_department_button").click(function(){
            $("#register_department_modal").modal('show');
        });
        $('.ui.dropdown').dropdown();
        $('.ui.checkbox').checkbox();
        $('#register_department_form')
            .form({
                fields: {
                    name: {
                        identifier: 'name',
                        rules: [
                            {
                                type   : 'minLength[4]',
                                prompt : 'The name must be at least 4 characters'
                            },
                            {
                                type   : 'maxLength[32]',
                                prompt : 'The name cannot be longer than 32 characters'
                            }
                        ]
                    },
                    jobs: {
                        identifier: 'jobs',
                        rules: [
                            {
                                type   : 'minCount[2]',
                                prompt : 'Please select at least two jobs'
                            }
                        ]
                    }
                }
            })
        ;
    });
</script>