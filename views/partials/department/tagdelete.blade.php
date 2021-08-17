<div class="ui inverted segment">
    <h2 class="ui header">Delete a Tag</h2>
    <p>Delete a tag from a department.</p>
    <button id="department_manage_tag_delete_button" class="fluid ui red button">
        Delete
    </button>
</div>

<div id="department_manage_tag_delete_modal" class="ui modal">
    <div class="header">
        Delete a tag
    </div>
    <div class="content">
        <form id="department_manage_tag_delete_form" action="/department/{{ $department->GetID() }}/tag/delete" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <div class="ui selection dropdown">
                    <input type="hidden" name="tag">
                    <i class="dropdown icon"></i>
                    <div class="default text">Tag</div>
                    <div class="scrollhint menu">
                        <?php $baseTag = new Tag() ?>
                        @foreach($baseTag->GetAllForDepartment($department->GetID()) as $tag)
                            <div class="item" data-value="{{ $tag->GetID() }}">
                                <div class="ui {{ $tag->GetColor() }} empty circular label"></div>
                                {{ $tag->GetName() }} - {{ $tag->GetSlug() }}
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
        <button class="ui green right labeled icon button" type="submit" form="department_manage_tag_delete_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#department_manage_tag_delete_button").click(function(){
            $("#department_manage_tag_delete_modal").modal('show');
        });
        $('#department_manage_tag_delete_form')
            .form({
                fields: {
                    tag: {
                        identifier: 'tag',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : 'Please select a tag'
                            }
                        ]
                    }
                }
            })
        ;
    });
</script>