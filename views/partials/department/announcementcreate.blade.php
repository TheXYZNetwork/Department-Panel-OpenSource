<div class="ui inverted segment">
    <h2 class="ui header">Create an Announcement</h2>
    <p>Create an announcement for all department members.</p>
    <button id="department_manage_announcement_create_button" class="fluid ui blue button">
        Create
    </button>
</div>

<div id="department_manage_announcement_create_modal" class="ui modal">
    <div class="header">
        Create an Announcement
    </div>
    <div class="content">
        <form id="department_manage_announcement_create_form" action="/department/{{ $department->GetID() }}/announcement/create" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>Announcement Title</label>
                <input type="text" name="title" placeholder="We're looking for higher-ups!">
            </div>
            <div class="field">
                <label>Announcement Content</label>
                <textarea type="text" name="content" placeholder="Hey everyone!&#10;&#10;We're looking to take on some new higher-ups, if you're interested please complete the 'Higher-Up' form found in the form section of the website!"></textarea>
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="department_manage_announcement_create_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#department_manage_announcement_create_button").click(function(){
            $("#department_manage_announcement_create_modal").modal('show');
        });
        $('.ui.checkbox').checkbox();
        $('#department_manage_announcement_create_form')
            .form({
                fields: {
                    title: {
                        identifier: 'title',
                        rules: [
                            {
                                type   : 'minLength[4]',
                                prompt : 'The name must be at least 4 characters'
                            },
                            {
                                type   : 'maxLength[64]',
                                prompt : 'The name cannot be longer than 46 characters'
                            }
                        ]
                    },
                    content: {
                        identifier: 'content',
                        rules: [
                            {
                                type   : 'minLength[4]',
                                prompt : 'The content must be at least 4 characters'
                            },
                            {
                                type   : 'maxLength[1000]',
                                prompt : 'The content cannot be longer than 1000 characters'
                            }
                        ]
                    },
                }
            })
        ;
    });
</script>