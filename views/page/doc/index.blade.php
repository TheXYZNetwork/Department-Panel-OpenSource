@extends('layouts.app')

@section('title', 'Documents')

@section('content')
    <h1 class="ui header">Documents</h1>

    <?php $department = new Department; ?>

    <div id="departments" class="ui grid">
        @foreach($department->GetAll() as $dep)
            <div class="four wide column">
                <a class="ui inverted segment" href="/department/{{ $dep->GetID() }}/docs" style="text-align: left; display: block;">
                    <h3 class="ui header">{{ $dep->GetName() }}</h3>
                    {{ number_format(count($dep->GetDocuments())) }} documents.
                </a>
            </div>
        @endforeach
    </div>

    <script>
        // Auto click if there's only 1 option
        $(document).ready(function() {
            let possibleDeps = $('#departments').children();
            if (!possibleDeps[1]) {
                possibleDeps[0].childNodes[1].click()
            }
        })
    </script>
@endsection