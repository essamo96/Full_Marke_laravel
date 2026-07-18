<div class="d-flex align-items-center">
    <div class="symbol symbol-50px me-3">
        @php
            $imagePath = $group->subject && $group->subject->image ? (str_starts_with($group->subject->image, 'site/') ? asset($group->subject->image) : asset('storage/' . $group->subject->image)) : asset('site/images/img/logo_backup.png');
        @endphp
        <img src="{{ $imagePath }}" class="" alt="{{ $group->name }}" />
    </div>
    <div class="d-flex justify-content-start flex-column">
        <span class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 view-group-details" data-id="{{ Crypt::encrypt($group->id) }}" style="cursor:pointer;">{{ $group->name }}</span>
        @if($group->subject)
            <span class="text-gray-400 fw-semibold d-block fs-7">{{ $group->subject->name }}</span>
        @endif
    </div>
</div>
