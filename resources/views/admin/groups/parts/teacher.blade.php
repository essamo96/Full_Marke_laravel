@if($group->teacher)
<div class="d-flex align-items-center">
    <div class="symbol symbol-40px symbol-circle me-3">
        @php
            $imagePath = $group->teacher->photo ? (str_starts_with($group->teacher->photo, 'site/') ? asset($group->teacher->photo) : asset('storage/' . $group->teacher->photo)) : asset('assets/admin/media/avatars/blank.png');
        @endphp
        <img src="{{ $imagePath }}" class="" alt="{{ $group->teacher->name }}" />
    </div>
    <div class="d-flex justify-content-start flex-column">
        <span class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6 view-group-details" data-id="{{ Crypt::encrypt($group->id) }}" style="cursor:pointer;">{{ $group->teacher->name }}</span>
        @php
            $dayNames = ['sat' => 'السبت', 'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء', 'thu' => 'الخميس', 'fri' => 'الجمعة'];
            $groupDays = is_array($group->days) ? $group->days : json_decode($group->days, true);
            $mappedDays = collect($groupDays)->map(fn($day) => $dayNames[$day] ?? $day)->implode('، ');
        @endphp
        @if($mappedDays || $group->start_time)
            <span class="text-muted fw-semibold d-block fs-7" dir="rtl">
                <i class="bi bi-calendar-event me-1"></i> {{ $mappedDays }}
                @if($group->start_time && $group->end_time)
                    | <i class="bi bi-clock me-1"></i> {{ \Carbon\Carbon::parse($group->start_time)->format('h:i A') }} - {{ \Carbon\Carbon::parse($group->end_time)->format('h:i A') }}
                @endif
            </span>
        @endif
    </div>
</div>
@else
<span class="text-gray-400">لا يوجد مدرس</span>
@endif
