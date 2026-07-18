<div class="d-flex align-items-center justify-content-center mt-3">
    @php
        $count = $group->registrations()->count();
        $isFull = $count >= $group->max_capacity;
        $btnClass = $isFull ? 'btn-light-danger' : 'btn-light-primary';
    @endphp
    <a href="{{ route('groups.students', Crypt::encrypt($group->id)) }}" class="btn btn-sm {{ $btnClass }} d-flex align-items-center px-4 w-100 justify-content-center fw-bold" data-bs-toggle="tooltip" title="عرض الطلاب">
        <i class="ki-outline ki-profile-user fs-2 me-2"></i>
        <span class="fs-6" dir="ltr">{{ $count }} / {{ $group->max_capacity }}</span>
    </a>
</div>
