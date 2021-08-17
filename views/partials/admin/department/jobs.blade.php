<div class="ui inverted segment">
    <h2 class="ui header">Set A Department's Jobs</h2>
    <p>Set what jobs are part of a department.</p>
    <button id="jobs_department_button" class="fluid ui brown button">
        Set
    </button>
</div>

<div id="jobs_department_modal" class="ui modal">
    <div class="header">
        Set A Department's Jobs
    </div>
    <div class="content">
        <form id="jobs_department_form" action="/admin/department/jobs" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Department</label>
                <select id="jobs_department_select" name="department" class="ui search dropdown">
                    <option value="">Select A Department</option>
                    <?php $departments = new Department(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($departments->GetAll() as $dep)
                        <option value="{{ $dep->GetID() }}">{{ $dep->GetName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Jobs</label>
                <select id="jobs_department_select_jobs" name="jobs[]" multiple="" class="ui search three column dropdown">
                    <option value="">Select Jobs</option>
                    <?php $jobs = new Job(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($jobs->GetAll() as $job)
                        <option value="{{ $job->GetClass() }}">{{ $job->GetName() }}</option>
                    @endforeach
                </select>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="jobs_department_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#jobs_department_button").click(function(){
            $("#jobs_department_modal").modal('show');
        });
        $("#jobs_department_select").on('change', function() {
            $('#jobs_department_select_jobs').dropdown('clear');

            var jobIDs = [];
            for (var key in deps[this.value]) {
                jobIDs.push(key);
            }

            $('#jobs_department_select_jobs').dropdown('set selected', jobIDs); // Pre populate with existing jobs
        });
        $('.ui.dropdown').dropdown();
        $('#jobs_department_form')
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