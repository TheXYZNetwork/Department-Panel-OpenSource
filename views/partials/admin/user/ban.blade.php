<div class="ui inverted segment">
    <h2 class="ui header">Ban a User</h2>
    <p>Ban a user from the site.</p>
    <button id="ban_user_button" class="fluid ui red button">
        Ban
    </button>
</div>

<div id="ban_user_modal" class="ui modal">
    <div class="header">
        Ban a User
    </div>
    <div class="content">
        <form id="ban_user_form" action="/admin/user/ban" method="POST" class="ui form">
            <div class="ui error message"></div>
            <div class="field">
                <label>User's SteamID64</label>
                <input placeholder="SteamID64" name="steamid64" type="text">
            </div>
        </form>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
        <button class="ui green right labeled icon button" type="submit" form="ban_user_form" value="Submit">
            Lets do it!
            <i class="checkmark icon"></i>
        </button>
    </div>
</div>

<script>
    $(document).ready(function(){
        $("#ban_user_button").click(function(){
            $("#ban_user_modal").modal('show');
        });
        $('.ui.dropdown').dropdown();
        $('#ban_user_form')
            .form({
                fields: {
                    steamid64: {
                        identifier: 'steamid64',
                        rules: [
                            {
                                type   : 'minLength[17]',
                                prompt : 'This is not a valid SteamID64'
                            },
                            {
                                type   : 'maxLength[17]',
                                prompt : 'This is not a valid SteamID64'
                            }
                        ]
                    }
                }
            })
        ;
    });
</script>