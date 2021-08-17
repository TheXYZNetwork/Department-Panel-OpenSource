<div class="ui inverted segment">
    @if(!$form->IsPublished())
        <div class="right aligned floating ui red label">Unpublished</div>
    @endif

    <h2 class="ui header">{{ $form->GetTitle() }}</h2>
    <p>{{ $form->GetDescription() }}</p>
    <a href="/forms/{{ $form->GetID() }}" class="fluid ui blue button">
        Complete
    </a>
</div>