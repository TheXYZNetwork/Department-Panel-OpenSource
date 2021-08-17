<div class="ui inverted segment">
    @if(!$doc->IsPublished())
        <div class="right aligned floating ui red label">Unpublished</div>
    @endif

    <div class="ui fluid" style="max-height: 250px; overflow: hidden;">
        <div id="thumbnail_{{ $doc->GetID() }}" class="blur">
        </div>
    </div>

    <h2 class="ui header">{{ $doc->GetTitle() }}</h2>
    <p>{{ $doc->GetDescription() }}</p>
    <a href="/docs/{{ $doc->GetID() }}" class="fluid ui orange button">
        View
    </a>

    <script>
        var quill;
        var data;
        $(document).ready(function() {
            quill = new Quill('#thumbnail_{{ $doc->GetID() }}', {});

            data = <?= $doc->GetContents() ?>;
            quill.setContents(data);
            quill.enable(false);
        });
    </script>
</div>