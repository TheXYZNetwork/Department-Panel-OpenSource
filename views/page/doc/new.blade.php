@extends('layouts.app')

@section('title', 'New Document')

<?php
$department = new Department($depID);
$document = isset($docID) ? new Document($docID) : false;
?>

@section('content')
    <h1 class="ui header">Documents - {{ $department->GetName() }}</h1>

    @if($document)
        <h2 class="ui header">Edit this document here! You can revisit this at any time!</h2>
    @else
        <h2 class="ui header">Create a new document here! You can edit this document at any time!</h2>
    @endif

    <form id="form_new_document" action="{{ $document  ? "/docs/" . $document->GetID() . "/edit" : "/docs/$depID/create" }}" method="POST" class="ui form">
        <div class="ui error message"></div>
        <div class="ui inverted segment">
            <div class="field">
                <label>Title</label>
                <input type="text" name="title" placeholder="Title" @if ($document) value="{{ $document->GetTitle() }}" @endif>
            </div>

            <div class="field">
                <label>Description</label>
                <input type="text" name="desc" placeholder="Description" @if ($document) value="{{ $document->GetDescription() }}" @endif>
            </div>

            <div class="field">
                <label>Contents</label>
                <textarea style="display: none" id="form_new_document_contents" name="contents"></textarea>
                <div id="editor">
                    <h1 class="ql-align-center">{{ $department->GetName() }}</h1>
                    <h2 class="ql-align-center">| Handbook |</h2>
                    <h3 class="ql-align-center">Written by <b>{{ $me->GetName() }}</b></h3>
                    <br>
                    <h2>Contents</h2>
                    <ul>
                        <li>Cool Thing 1 </li>
                        <li>Cool Thing 2 </li>
                    </ul>
                    <br>
                    <h2>Cool Thing 1</h2>
                    This is a very cool thing we should all be doing!
                    <br>
                    <br>
                    <h2>Cool Thing 2</h2>
                    This is the second cool thing, it's not as cool as the first...
                    <br>
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
                        <label>Interaction</label>
                        <div class="ui checkbox">
                            <input name="interaction" type="checkbox" @if ($document) {{ $document->GetInteractability() ? 'checked=""' : "" }} @endif>
                            <label>Allow comments and reactions</label>
                        </div>
                    </div>
                </div>
            </div>

            <button type="submit" class="ui fluid {{ $document  ? "yellow" : "primary" }} button">
                {{ $document  ? "Edit" : "Create" }} Document
            </button>
        </div>
    </form>

    <style>
        .ql-toolbar {
            background-color: white;
        }
        .ql-container {
            background-color: white;
            color: black;
            max-height: 500px;
            overflow: scroll;
        }
    </style>
    <script>
        var quill
        $(document).ready(function(){
            quill = new Quill('#editor', {
                modules: {
                    toolbar: toolbarOptions
                },
                placeholder: 'Please add content to your document!',
                theme: 'snow'  // or 'bubble'
            });

            @if ($document)
                data = <?= $document->GetContents() ?>;
                quill.setContents(data);
            @endif

            quill.on('text-change', function(delta, oldDelta, source) {
                $('#form_new_document_contents').val(JSON.stringify(quill.getContents()));
            });
            $('#form_new_document_contents').val(JSON.stringify(quill.getContents()));

            @if ($document)
                $('#viewability_dropdown').dropdown('set selected', [ '{!! implode("','", $document->GetViewability()) !!}' ])
            @endif

            $('.ui.dropdown')
                .dropdown()
            ;
            $('#form_new_document')
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
                                    type   : 'maxLength[128]',
                                    prompt : 'The name cannot be longer than 32 characters'
                                }
                            ]
                        },
                        desc: {
                            identifier: 'desc',
                            rules: [
                                {
                                    type   : 'minLength[4]',
                                    prompt : 'The description must be at least 4 characters'
                                },
                                {
                                    type   : 'maxLength[500]',
                                    prompt : 'The description cannot be longer than 64 characters'
                                }
                            ]
                        },
                        contents: {
                            identifier: 'contents',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : 'Please include some content in your document'
                                }
                            ]
                        },
                        viewability: {
                            identifier  : 'viewability[]',
                            rules: [
                                {
                                    type   : 'empty',
                                    prompt : 'Please select a dropdown value'
                                }
                            ]
                        },
                    }
                })
            ;
        });
    </script>

@endsection