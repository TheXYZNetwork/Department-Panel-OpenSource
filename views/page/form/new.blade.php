@extends('layouts.app')

@section('title', 'New Form')

<?php
$department = new Department($depID);
$form = isset($formID) ? new Form($formID) : false;
?>

@section('content')
    <h1 class="ui header">Forms - {{ $department->GetName() }}</h1>

    @if($form)
        <h2 class="ui header">Edit this form here! You can revisit this at any time!</h2>
    @else
        <h2 class="ui header">Create a new form here! You can edit this form at any time!</h2>
    @endif

    <form id="create_new_form" class="ui form">
        <div class="ui inverted segment">
            <div class="field">
                <label>Title</label>
                <input id="form_name" type="text" name="title" placeholder="Title" @if($form) value="{{$form->GetTitle()}}" @endif()>
            </div>

            <div class="field">
                <label>Description</label>
                <input id="form_desc" type="text" name="desc" placeholder="Description" @if($form) value="{{$form->GetDescription()}}" @endif()>
            </div>

            <div class="field">
                <label>Elements</label>
                <div id="elements_list">
                </div>

                <div class="ui divider"></div>

                <div class="ui selection dropdown">
                    <input id='element_type' type="hidden">
                    <i class="dropdown icon"></i>
                    <div class="default text">Choose an element!</div>
                    <div id='element_options' class="scrollhint menu">
                    </div>
                </div>
            </div>

            <div class="field">
                <div class="two fields">
                    <div class="field">
                        <label>Viewability</label>
                        <select id="viewability_dropdown" name="viewability[]" multiple="" class="ui search two column dropdown">
                            <option value="">Select Jobs</option>
                            <?php $jobs = new Job(); // Would prefer to do this in blade but can't :/ ?>
                            <option value="*">Anyone</option>
                            <option value="$">All Department Members</option>
                            @foreach ($department->GetJobs() as $job)
                                <option value="!{{ $job->GetClass() }}">[Job] {{ $job->GetName() }}</option>
                            @endforeach
                            @foreach ($department->GetTags() as $tag)
                                <option value="#{{ $tag->GetID() }}">[Tag] {{ $tag->GetName() }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="field">
                        <label>Edit/Response Viewability</label>
                        <select id="response_viewability_dropdown" name="response_viewability[]" multiple="" class="ui search two column dropdown">
                            <option value="">Select Jobs</option>
                            <?php $jobs = new Job(); // Would prefer to do this in blade but can't :/ ?>
                            <option value="$">All Department Members</option>
                            @foreach ($department->GetJobs() as $job)
                                <option value="!{{ $job->GetClass() }}">[Job] {{ $job->GetName() }}</option>
                            @endforeach
                            @foreach ($department->GetTags() as $tag)
                                <option value="#{{ $tag->GetID() }}">[Tag] {{ $tag->GetName() }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            <div type="button" onclick="processFormCreation();" class="ui fluid {{ $form ? "yellow" : "primary" }} button">
                {{ $form ? "Edit" : "Create" }} Form
            </div>
        </div>
    </form>

    <script>
        // This is a real ball sniffer to get working, easily top 10 worst js ever written.
        var options = {
            ['text']: {
                ['id']: "text",
                ['name']: "Single Line Text",
                ['desc']: "An open ended one line user input."
            },
            ['paragraph']: {
                ['id']: "paragraph",
                ['name']: "Paragraph",
                ['desc']: "An open ended paragraph user input with formatting and styling."
            },
            ['calendar']: {
                ['id']: "calendar",
                ['name']: "Calendar",
                ['desc']: "A calendar to choose a date from.",
                ['extract']: function(container) {
                    return 0;
                }
            },
            ['dropdown']: {
                ['id']: "dropdown",
                ['name']: "Dropdown Box",
                ['desc']: "Select an answer from a preset list.",
                ['builder']: `
<h5>Dropdown Options</h5>

<div class="dropdown_list">
</div>

<div class="ui hidden divider"></div>

<div class="ui action input">
  <input type="text" placeholder="Add an option for this dropdown">
  <button type="button" onclick="addToDropdownChoice(this);" class="dropdown_add ui teal button">Add</button>
</div>
                `,
                ['extract']: function(container) {
                    var list = $(container.children()[3]);
                    var choices = [];

                    $.each(list.children(), function(key, val) {
                        var container = $(val);
                        var content = $(container.children()[1]).text();

                        choices.push(content);
                    })

                    return choices;
                }
            },
            ['multichoice']: {
                ['id']: "multichoice",
                ['name']: "Multi-Choice",
                ['desc']: "Select multiple answers from a preset list.",
                ['builder']: `
<h5>Multi-choice Options</h5>

<div class="dropdown_list">
</div>

<div class="ui hidden divider"></div>

<div class="ui action input">
  <input type="text" placeholder="Add an option for this multi-choice">
  <button type="button" onclick="addToDropdownChoice(this);" class="dropdown_add ui teal button">Add</button>
</div>
                `,
                ['extract']: function(container) {
                    var list = $(container.children()[3]);
                    var choices = [];

                    $.each(list.children(), function(key, val) {
                        var container = $(val);
                        var content = $(container.children()[1]).text();

                        choices.push(content);
                    })

                    return choices;
                }
            },
        }

        @if($form)
            var existingOptions = {
                @foreach($form->GetElements() as $elementKey => $element)
                [{{ $elementKey }}]: {
                    ['type']: "{{ $element['type'] }}",
                    ['title']: "{{ $element['title'] }}",
                    ['data']: JSON.parse(`{!! json_encode($element['data']) !!}`)
                },
                @endforeach
            }
        @endif

        function addToDropdownChoice(self) {
            self = $(self);

            var text = $(self.parent().children()[0]).val()
            if(text == "") return;

            var listTarget = $(self.parent().parent().children()[3]);

            listTarget.append(`
<div class="ui inverted segment">
    <button type="button" onclick="removeParent(this);" class="mini ui right floated red button">X</button>

    <p>${ text }</p>
</div>
            `);
        }

        function removeParent(self) {
            self = $(self);

            var parent = $(self.parent())

            parent.remove()
        }

        $(document).ready(function(){
            $('.ui.dropdown')
                .dropdown()
            ;

            for(var option in options) {
                var data = options[option];
                $('#element_options').append(`<div class="item" data-value="${ option }">${ data['name'] }</div>`);
            }

            $("#element_type").change(function() {
                let basis = $('#elements_list').append(`
<div class="ui segment" data-type="${ options[$("#element_type").val()]['id'] }">
    <h4 class="ui header">
      ${ options[$("#element_type").val()]['name'] }
      <div class="sub header">${ options[$("#element_type").val()]['desc'] }</div>
    </h4>

    <div class="ui input">
      <input data-validate="title" type="text" placeholder="Enter a name for this element">
    </div>

    ${ options[$("#element_type").val()]['builder'] ?? '' }

    <h4 class="ui horizontal divider header">
        Actions
    </h4>

    <button type="button" onclick="removeParent(this);" class="fluid ui red button">Remove</button>
    </div>
</div>
                `);

                $("#element_type").val('');
            });

            $('#elements_list').sortable({
                group: 'list',
                ghostClass: 'blue'
            });

            @if($form)
                // A very hacky way of doing things :/ I wish to be dead
                $.each(existingOptions, function(key, val) {
                    $("#element_type").val(val["type"]);
                    $("#element_type").trigger("change");

                    var elements = $('#elements_list');;

                    var elmnt = $(elements.children()[key]);
                    // Update the name
                    var elmntNameInput = $(elmnt.children()[1]);
                    var elmntName = $(elmntNameInput.children()[0]).val(val["title"]); // Element name string
                    // Populate the input and trigger the add
                    var elmntAddAction = true;

                    if(elmnt.children()[5]) {
                        var inputParent = $(elmnt.children()[5]);
                        var input = $(inputParent.children()[0]);
                        var addBtn = $(inputParent.children()[1]);

                        $.each(val['data'], function(dataKey, dataVal) {
                            input.val(dataVal);
                            addBtn.click();
                        })
                        input.val('');
                    }
                })

            $('#viewability_dropdown').dropdown('set selected', [ '{!! implode("','", $form->GetViewability()) !!}' ])
            $('#response_viewability_dropdown').dropdown('set selected', [ '{!! implode("','", $form->GetResponseViewability()) !!}' ])
            @endif
        });

        function processFormCreation() {
            var elements = $('#elements_list');
            var data = {};
            $.each(elements.children(), function(key, val) {
                var elmnt = $(val);
                var elmntType = elmnt.attr('data-type'); // Element type string
                // Name
                var elmntNameInput = $(elmnt.children()[1])
                var elmntName = $(elmntNameInput.children()[0]).val() // Element name string
                // Excess data

                data[key] = {
                    ['type']: elmntType,
                    ['name']: elmntName,
                    ['input']: elmntNameInput,
                    ['data']: options[elmntType]['extract'] ? options[elmntType]['extract'](elmnt) : {}
                }
            })

            for(var dat in data) {
                var d = data[dat];
                if (d['name'] == "") {
                    $(d['input'].parent()).addClass('tertiary inverted red');
                    return
                } else {
                    $(d['input'].parent()).removeClass('tertiary inverted red');
                }
                console.log($(d['input'].parent()));
            }

            var formName = $('#form_name').val();
            var formDesc = $('#form_desc').val();
            var formViewability = $('#viewability_dropdown').val();
            var formResponseViewability = $('#response_viewability_dropdown').val();

            var form = $(`
<form action="{{  $form ? "/forms/" . $form->GetID() . "/edit" : "/forms/" . $depID . "/create" }}" method="post">
    <input type="text" name="title" value="${ formName }" />
    <input type="text" name="desc" value="${ formDesc }" />
    <input type="text" name="viewability[]" value="${ formViewability }" />
    <input type="text" name="response_viewability[]" value="${ formResponseViewability }" />
    <input id="temp_form_elements_input" type="text" name="elements" />
</form>
            `);
            $('body').append(form);

            $('#temp_form_elements_input').val(JSON.stringify(data)); // We do this in js because we can't easily escape the quotes.
            form.submit();
        }
    </script>

@endsection