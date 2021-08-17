<div class="ui inverted segment">
    <h2 class="ui header">Set A Department's Identifier</h2>
    <p>Set the identifier for a department.</p>
    <button id="identifier_department_button" class="fluid ui olive button">
        Set
    </button>
</div>

<div id="identifier_department_modal" class="ui modal">
    <div class="header">
        Set A Department's Identifier
    </div>
    <div class="content">
        <form id="identifier_department_form" action="/admin/department/identifier" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Department</label>
                <select id="identifier_department_select" name="department" class="ui search dropdown">
                    <option value="">Select A Department</option>
                    <?php $departments = new Department(); // Would prefer to do this in blade but can't :/ ?>
                    @foreach ($departments->GetAll() as $dep)
                        <option value="{{ $dep->GetID() }}">{{ $dep->GetName() }}</option>
                    @endforeach
                </select>
            </div>
            <div class="field">
                <label>Identifier</label>
                <input type="text" name="identifier" placeholder="pd">
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="identifier_department_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#identifier_department_button").click(function(){
            $("#identifier_department_modal").modal('show');
        });
        $('.ui.dropdown').dropdown();
    });
</script>