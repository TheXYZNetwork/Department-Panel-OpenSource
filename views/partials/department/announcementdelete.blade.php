<div class="ui inverted segment">
    <h2 class="ui header">Delete an Announcement</h2>
    <p>Delete an announcement.</p>
    <button id="department_manage_announcement_delete_button" class="fluid ui red button">
        Delete
    </button>
</div>

<div id="department_manage_announcement_delete_modal" class="ui modal">
    <div class="header">
        Delete an Announcement
    </div>
    <div class="content">
        <form id="department_manage_announcement_delete_form" action="/department/{{ $department->GetID() }}/announcement/delete" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Announcement</label>
                <select name="announcement" class="ui dropdown">
                    <option value="">Select An Announcement</option>
                    @foreach ($department->GetRecentAnnouncements() as $announcement)
                        <option value="{{ $announcement['id'] }}">{{ $announcement['title'] }}</option>
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
        <button class="ui green right labeled icon button" type="submit" form="department_manage_announcement_delete_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#department_manage_announcement_delete_button").click(function(){
            $("#department_manage_announcement_delete_modal").modal('show');
        });
        $('.ui.checkbox').checkbox();
        $('.ui.dropdown').dropdown();
    });
</script>