<div class="ui fullscreen modal" id="modal_points">
    <div class="header">Points</div>
    <div class="scrolling content">
        <div class="ui two column grid">
            <div class="ten wide column" id="modal_points_content">
            </div>
            <div class="six wide column">
                <form id="points_form" action="/roster/{{ $department->GetID() }}/points/give" method="POST" autocomplete="off">
                    <input type="hidden" id="points_form_user" name="userid">
                    <div class="ui form">
                        <div class="ui error message"></div>
                        <div class="field">
                            <label>Amount</label>
                            <div class="ui input">
                                <input type="number" placeholder="30" name="amount">
                            </div>
                        </div>
                        <div class="field">
                            <label>Reason</label>
                            <div class="ui input">
                                <input type="text" placeholder="Abusing police authority" name="reason">
                            </div>
                        </div>
                        <div class="two fields">
                            <div class="field">
                                <label>Expires</label>
                                <div class="ui calendar disabled" id="points_date_calendar">
                                    <div class="ui input left icon">
                                        <i class="calendar icon"></i>
                                        <input type="text" placeholder="Date/Time" name="date">
                                    </div>
                                </div>
                            </div>
                            <div class="field">
                                <div class="ui toggle checkbox" id="points_form_expire">
                                    <input type="checkbox" tabindex="0" class="hidden" name="expires">
                                    <label>Expires</label>
                                </div>
                            </div>
                        </div>
                        <button class="ui green right labeled icon button" form="points_form" id="points_form_submit">
                            Lets do it!
                            <i class="checkmark icon"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <div class="actions">
        <div class="ui black deny right labeled icon button">
            Nevermind
            <i class="times icon"></i>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.roster_button_points').click(async function() {
            $('#modal_points').modal('show');

            var today = new Date();
            $('#points_date_calendar')
                .calendar({
                    type: 'date',
                    minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
                    maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() + {{ $config->get('Tag Expire Max Length') }})
                })
            ;

            $('.ui.checkbox').checkbox();

            $("#points_form_user").val(this.getAttribute("data-steamid"))

            $("#points_form_submit").click(function() {
                $("#points_form").submit();
            });

            $('#points_form_expire').click(function() {
                if ($('#points_form_expire').hasClass("checked")) {
                    $('#points_date_calendar').removeClass('disabled')
                } else {
                    $('#points_date_calendar').addClass('disabled')
                }
            })

            let contents = $('#modal_points_content');
            contents.empty();

            contents.html(`
                <div class="ui active inverted dimmer">
                    <div class="ui large text loader">Loading points</div>
                </div>
            `)

            let plyID = this.getAttribute("data-steamid")
            let points = await GetMemberPoints(plyID, '{{ $department->GetID() }}');
            contents.empty();
            console.log(points);

            if (points.error) {
                contents.html(`
                    <div class="ui negative message">
                        <div class="header">
                            An error has occurred!
                        </div>
                        <p>${points.error}</p>
                    </div>
                `)
                return;
            }

            points.forEach(function(data) {
                let comments = []
                data.comments.forEach(function(commentData) {
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
    <div class="right aligned floating ui ${ (!data.expires || (data.expires > Math.round((new Date()).getTime() / 1000))) ? 'green' : 'red' } label">${ (!data.expires || (data.expires > Math.round((new Date()).getTime() / 1000))) ? 'Active' : 'Expired' }</div>
    <div class="ui grid">
        <div class="two wide column">
            <h4 class="ui inverted header">Amount
                <div class="sub header">${ data.amount }</div>
            </h4>
        </div>
        <div class="three wide column">
            <h4 class="ui inverted header">Reason
                <div class="sub header">${ data.reason }</div>
            </h4>
        </div>
        <div class="three wide column">
            <h4 class="ui inverted header">Officer
                <div class="sub header">${ data.officer.name }</div>
            </h4>
        </div>
        <div class="two wide column">
            <h4 class="ui inverted header">Expires
                <div class="sub header">${ data.expires ? timeConverter(parseInt(data.expires)) : "Never" }</div>
            </h4>
        </div>
        <div class="three wide column">
            <h4 class="ui inverted header">When
                <div class="sub header">${ timeago.format(parseInt(data.created) * 1000) }</div>
            </h4>
        </div>
        <div class="two wide column">
            <h4 class="ui inverted header">Actions
                <div class="sub header">
                    <form action="/roster/{{ $department->GetID() }}/${ data.user.steamid64 }/points/${ data.id }/remove" method="POST">
                        <button type="submit" class="mini ui red button">X</button>
                    </form>
                </div>
            </h4>
        </div>
    </div>
    <div class="ui inverted comments">
        ${ comments.join("") }
    </div>
    <form action="/roster/{{ $department->GetID() }}/points/${ data.id }/comment/add" method="POST" class="ui form">
        <div class="ui fluid action input">
            <input type="text" placeholder="Add a comment to these points" name="comment">
            <button class="ui teal button">Post</button>
        </div>
    </form>
</div>
                `)
            })

            $('.points_remove').click(async function() {
                console.log(this.getAttribute("data-pointsid"), plyID);
                await $.ajax({
                    url: `/roster/{{ $department->GetID() }}/${plyID}/points/${this.getAttribute("data-pointsid")}/remove`,
                    type: "POST"
                });

                location.reload();
            })

            $('.activating.element')
                .popup()
            ;
        })
        $('.ui.dropdown')
            .dropdown()
        ;
        $('#points_form')
            .form({
                fields: {
                    amount: {
                        identifier: 'amount',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : 'Please provide an amount of points to give.'
                            },
                            {
                                type   : 'integer[10..100]',
                                prompt : 'You must give between 10 and 100 points.'
                            }
                        ]
                    },
                    reason: {
                        identifier: 'reason',
                        rules: [
                            {
                                type   : 'empty',
                                prompt : 'Please provide a reason for these points.'
                            },
                            {
                                type   : 'minLength[6]',
                                prompt : 'Your reason must be longer...'
                            },
                            {
                                type   : 'maxLength[60]',
                                prompt : 'Your reason must be shorter...'
                            }
                        ]
                    }
                }
            })
        ;
        async function GetMemberPoints(id, department) {
            return $.ajax({
                url: `/api/points/${id}/${department}`,
                type: "GET"
            });
        };
    })
</script>