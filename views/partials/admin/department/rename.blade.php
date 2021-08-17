<div class="ui inverted segment">
    <h2 class="ui header">Rename A Department</h2>
    <p>Rename an already registered department.</p>
    <button id="rename_department_button" class="fluid ui orange button">
        Rename
    </button>
</div>

<div id="rename_department_modal" class="ui modal">
    <div class="header">
        Rename A Department
    </div>
    <div class="content">
        <form id="rename_department_form" action="/admin/department/rename" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Department</label>
                <select name="department" class="ui search dropdown">
                    <option value="">Select A Department</option>
                    <?php $departments = new Department(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($departments->GetAll() as $dep)
                        <option value="{{ $dep->GetID() }}">{{ $dep->GetName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>New Name</label>
                <input placeholder="Police Department" name="name" type="text">
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="rename_department_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#rename_department_button").click(function(){
            $("#rename_department_modal").modal('show');
        });
        $('.ui.dropdown').dropdown();
        $('#rename_department_form')
            .form({
                fields: {
                    name: {
                        identifier: 'name',
                        rules: [
                            {
                                type   : 'minLength[4]',
                                prompt : 'The name must be atleast 4 characters'
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