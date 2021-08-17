<div class="ui modal" id="modal_tags">
    <div class="header">Tags</div>
    <div class="content">
        <div class="ui two column grid">
            <div class="column" id="modal_tags_content">
            </div>
            <div class="column">
                <form id="tag_form" action="/roster/{{ $department->GetID() }}/tag/give" method="POST" autocomplete="off">
                    <input type="hidden" id="tag_form_user" name="userid">
                    <div class="ui form">
                        <div class="ui error message"></div>
                        <div class="field">
                            <label>Tag</label>
                            <div class="ui selection dropdown">
                                <input type="hidden" name="tag">
                                <i class="dropdown icon"></i>
                                <div class="default text">Tag</div>
                                <div class="menu">
                                    <?php $baseTag = new Tag() ?>
                                    @foreach($baseTag->GetAllForDepartment($department->GetID()) as $tag)
                                        <div class="item tag_form_select" data-value="{{ $tag->GetID() }}" data-expires="{{ $tag->Expires() }}">
                                            <div class="ui {{ $tag->GetColor() }} empty circular label"></div>
                                            {{ $tag->GetName() }} - {{ $tag->GetSlug() }}
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="field">
                            <label>Expires</label>
                            <div class="ui calendar" id="tag_date_calendar">
                                <div class="ui input left icon">
                                    <i class="calendar icon"></i>
                                    <input type="text" placeholder="Date/Time" name="expire">
                                </div>
                            </div>
                        </div>
                        <button class="ui green right labeled icon button" form="tag_form" id="tag_form_submit">
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
        $('.roster_button_tags').click(async function() {
            $('#modal_tags').modal('show');

            var today = new Date();
            $('#tag_date_calendar')
                .calendar({
                    type: 'date',
                    minDate: new Date(today.getFullYear(), today.getMonth(), today.getDate()),
                    maxDate: new Date(today.getFullYear(), today.getMonth(), today.getDate() + {{ $config->get('Tag Expire Max Length')  }})
                })
            ;

            $("#tag_form_user").val(this.getAttribute("data-steamid"))

            $("#tag_form_submit").click(function() {
                $("#tag_form").submit();
            });

            $('.tag_form_select').click(function() {
                if (this.getAttribute("data-expires") == "1") {
                    $('#tag_date_calendar').removeClass('disabled')
                } else {
                    $('#tag_date_calendar').addClass('disabled')
                }
            })


            let contents = $('#modal_tags_content');
            contents.empty();

            contents.html(`
                <div class="ui active inverted dimmer">
                    <div class="ui large text loader">Loading Tags</div>
                </div>
            `)

            let plyID = this.getAttribute("data-steamid")
            let tags = await GetMemberTags(plyID, '{{ $department->GetID() }}');
            contents.empty();

            if (tags.error) {
                contents.html(`
                    <div class="ui negative message">
                        <div class="header">
                            An error has occurred!
                        </div>
                        <p>${tags.error}</p>
                    </div>
                `)
                return;
            }

            tags.forEach(function(data) {
                contents.append(`
<a class="ui ${ data.color } label" ${ data.expires ? "data-tooltip='Expires: " + timeConverter(data.expires) + " '" : "" }>
    ${ data.name }
    <div class="detail">${ data.slug }</div>
    <i class="delete icon tag_remove" data-tagid="${ data.id }"></i>
</a>
                `)
            })

            $('.tag_remove').click(async function() {
                console.log(this.getAttribute("data-tagid"), plyID);
                await $.ajax({
                    url: `/roster/{{ $department->GetID() }}/${plyID}/tag/${this.getAttribute("data-tagid")}/remove`,
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
        $('#tag_form')
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
        async function GetMemberTags(id, department) {
            return $.ajax({
                url: `/api/tags/${id}/${department}`,
                type: "GET"
            });
        };
    })
</script>