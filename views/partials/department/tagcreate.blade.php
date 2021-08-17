<div class="ui inverted segment">
    <h2 class="ui header">Create a Tag</h2>
    <p>Create a tag to be given to department members.</p>
    <button id="department_manage_tag_create_button" class="fluid ui olive button">
        Create
    </button>
</div>

<div id="department_manage_tag_create_modal" class="ui modal">
    <div class="header">
        Create a tag
    </div>
    <div class="content">
        <form id="department_manage_tag_create_form" action="/department/{{ $department->GetID() }}/tag/create" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="two fields">
                <div class="field">
                    <label>Name</label>
                    <input placeholder="Leave of Absence" name="name" type="text">
                </div>
                <div class="field">
                    <label>Slug</label>
                    <input placeholder="LOA" name="slug" type="text">
                </div>
            </div>
            <div class="two fields">
                <div class="field">
                    <div class="ui toggle checkbox">
                        <input type="checkbox" tabindex="0" class="hidden" name="expires">
                        <label>Expires</label>
                    </div>
                </div>
                <div class="field">
                    <div class="ui selection dropdown">
                        <input type="hidden" name="color">
                        <i class="dropdown icon"></i>
                        <div class="default text">Color</div>
                        <div class="scrollhint menu">
                            @foreach(["red", "orange", "yellow", "olive", "green", "teal", "blue", "violet", "purple", "pink", "brown", "grey", "black"] as $color)
                            <div class="item" data-value="{{ $color }}">
                                <div class="ui {{ $color }} empty circular label"></div>
                                {{ ucwords($color) }}
                            </div>
                            @endforeach
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
        <button class="ui green right labeled icon button" type="submit" form="department_manage_tag_create_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#department_manage_tag_create_button").click(function(){
            $("#department_manage_tag_create_modal").modal('show');
        });
        $('.ui.checkbox').checkbox();
        $('#department_manage_tag_create_form')
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
                    slug: {
                        identifier: 'slug',
                        rules: [
                            {
                                type   : 'minLength[1]',
                                prompt : 'The name must be at least 1 characters'
                            },
                            {
                                type   : 'maxLength[6]',
                                prompt : 'The name cannot be longer than 6 characters'
                            }
                        ]
                    },
                    color: {
                        identifier: 'color',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : 'Please select a color'
                            }
                        ]
                    }
                }
            })
        ;
    });
</script>