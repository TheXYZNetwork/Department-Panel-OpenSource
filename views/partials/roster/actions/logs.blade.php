<div class="ui modal" id="modal_logs">
    <div class="header">Logs</div>
    <div class="scrolling content" id="modal_logs_content">
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.roster_button_log').click(async function(){
            $('#modal_logs').modal('show');

            let contents = $('#modal_logs_content');
            contents.empty();

            contents.html(`
                <div class="ui active inverted dimmer">
                    <div class="ui large text loader">Loading Logs</div>
                </div>
            `)

            let logs = await GetRecentLogs(this.getAttribute("data-steamid"), '{{ $department->GetID() }}');
            contents.empty();

            if (logs.error) {
                contents.html(`
                    <div class="ui negative message">
                        <div class="header">
                            An error has occurred!
                        </div>
                        <p>${logs.error}</p>
                    </div>
                `)
                return;
            }

            logs.forEach(function(data) {
                let comments = []
                if (data.comments) {
                    data.comments.forEach(function (commentData) {
                        comments.push(`
    <div class="comment">
        <a class="avatar">
            <img src="${commentData.user.avatar}">
        </a>
        <div class="content">
            <a class="author">${escapeHtml(commentData.user.name)}</a>
            <div class="metadata">
              <span class="date">${timeago.format(parseInt(commentData.created) * 1000)}</span>
            </div>
            <div class="text">
                ${escapeHtml(commentData.comment)}
            </div>
        </div>
    </div>
                        `)
                    })
                }
                contents.append(`
<div class="ui inverted segment">
    <div class="right aligned floating ui ${ data.state == "Demotion" ? 'red' : 'green' } label">${ data.state }</div>
    <div class="ui grid">
        <div class="five wide column">
            <h4 class="ui inverted header">Job
                <div class="sub header">${ data.job }</div>
            </h4>
        </div>
        <div class="six wide column">
            <h4 class="ui inverted header">Officer
                <div class="sub header">${ data.promoter }</div>
            </h4>
        </div>
        <div class="five wide column">
            <h4 class="ui inverted header">When
                <div class="sub header">${ timeago.format(parseInt(data.time) * 1000) } (${ ConvertUnixToReadable(parseInt(data.time)) })</div>
            </h4>
        </div>
    </div>
    <div class="ui inverted comments">
        ${ comments.join("") }
    </div>
    <form action="/roster/{{ $department->GetID() }}/logs/${ data.id }/comment/add" method="POST" class="ui form">
        <div class="ui fluid action input">
            <input type="text" placeholder="Add a comment to this log" name="comment">
            <button class="ui teal button">Post</button>
        </div>
    </form>
</div>
                `)
            })
        });
    });

    async function GetRecentLogs(id, department) {
        return $.ajax({
            url: `/api/logs/${id}/${department}`,
            type: "GET"
        });
    };
</script>