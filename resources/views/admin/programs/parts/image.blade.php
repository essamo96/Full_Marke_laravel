@if ($program->image)
    <img src="{{ asset('storage/'.$program->image) }}" class="rounded" width="48" height="48" alt="{{ $program->title_en }}">
@else
    <span class="symbol symbol-48px"><span class="symbol-label bg-light-primary text-primary fw-bold">{{ mb_substr($program->title_en, 0, 1) }}</span></span>
@endif
