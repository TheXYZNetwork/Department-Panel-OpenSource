<div class="ui inverted segment">
    <h2 class="ui header">Set A Department's Higher-Ups</h2>
    <p>Set what jobs are higher-ups in a department, allowing them to use higher privilege tools.</p>
    <button id="higherups_department_button" class="fluid ui teal button">
        Set
    </button>
</div>

<div id="higherups_department_modal" class="ui modal">
    <div class="header">
        Set A Department's Higher-Ups
    </div>
    <div class="content">
        <form id="higherups_department_form" action="/admin/department/higherups" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Department</label>
                <select id="higherups_department_select" name="department" class="ui search dropdown">
                    <option value="">Select A Department</option>
                    <?php $departments = new Department(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($departments->GetAll() as $dep)
                        <option value="{{ $dep->GetID() }}">{{ $dep->GetName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Jobs</label>
                <select id="higherups_department_select_jobs" name="jobs[]" multiple="" class="ui search dropdown">
                    <option value="">Select Jobs</option>
                </select>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="higherups_department_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#higherups_department_button").click(function(){
            $("#higherups_department_modal").modal('show');
        });
        $("#higherups_department_select").on('change', function() {
            $('#higherups_department_select_jobs').empty();
            $('#higherups_department_select_jobs').dropdown('clear');

            for (var key in deps[this.value]) {
                let name = deps[this.value][key];

                $('#higherups_department_select_jobs').append('<option value="' + key + '">' + name + '</option>');
            }
        });
        $('.ui.dropdown').dropdown();
        $('#higherups_department_form')
            .form({
                fields: {
                    jobs: {
                        identifier: 'jobs',
                        rules: [
                            {
                                type   : 'minCount[1]',
                                prompt : 'Please select at least one jobs'
                            }
                        ]
                    }
                }
            })
        ;
    });
</script>