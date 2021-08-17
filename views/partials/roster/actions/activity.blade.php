<div class="ui modal" id="modal_activity">
    <div class="header">Activity</div>
    <div class="scrolling content" id="modal_activity_content">
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.roster_button_activity').click(async function(){
            $('#modal_activity').modal('show');

            let contents = $('#modal_activity_content');
            contents.empty();

            contents.html(`
                <div class="ui active inverted dimmer">
                    <div class="ui large text loader">Loading Activity</div>
                </div>
            `)

            let activity = await GetRecentActivity(this.getAttribute("data-steamid"), '{{ $department->GetID() }}');
            contents.empty();

            if (activity.error) {
                contents.html(`
                    <div class="ui negative message">
                        <div class="header">
                            An error has occurred!
                        </div>
                        <p>${activity.error}</p>
                    </div>
                `)
                return;
            }

            activity.forEach(function(data) {
                let comments = []
                data.comments.forEach(function(commentData) {
                    console.log(parseInt(commentData.created))
                    comments.push(`
<div class="comment">
    <a class="avatar">
        <img src="${ commentData.user.avatar }">
    </a>
    <div class="content">
        <a class="author">${ escapeHtml(commentData.user.name) }</a>
        <div class="metadata">
          <span class="date">${ timeago.format(parseInt(commentData.created) * 1000) }</span>
        </div>
        <div class="text">
            ${ escapeHtml(commentData.comment) }
        </div>
    </div>
</div>
                    `)
                })
                contents.append(`
<div class="ui inverted segment">
    <div class="ui grid">
        <div class="six wide column">
            <h4 class="ui inverted header">Job
                <div class="sub header">${ data.job }</div>
            </h4>
        </div>
        <div class="five wide column">
            <h4 class="ui inverted header">Session
                <div class="sub header">${ secondsToHms(data.leave - data.join) }</div>
            </h4>
        </div>
        <div class="five wide column">
            <h4 class="ui inverted header">When
                <div class="sub header">${ timeago.format(parseInt(data.join) * 1000) } (${ ConvertUnixToReadable(parseInt(data.join)) })</div>
            </h4>
        </div>
    </div>
    <div class="ui inverted comments">
        ${ comments.join("") }
    </div>
    <form action="/roster/{{ $department->GetID() }}/activity/${ data.id }/comment/add" method="POST" class="ui form">
        <div class="ui fluid action input">
            <input type="text" placeholder="Add a comment to this activity" name="comment">
            <button class="ui teal button">Post</button>
        </div>
    </form>
</div>
                `)
            })
        });
    });

    async function GetRecentActivity(id, department) {
        return $.ajax({
            url: `/api/activity/${id}/${department}`,
            type: "GET"
        });
    };
</script>