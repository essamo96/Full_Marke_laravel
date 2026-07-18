<div class="modal-header">
    <h3 class="modal-title">تفاصيل المجموعة: {{ $group->name }}</h3>
    <div class="btn btn-icon btn-sm btn-active-light-primary ms-2" data-bs-dismiss="modal" aria-label="Close">
        <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
    </div>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-6">
            <h4 class="mb-4">المدرس</h4>
            @if($group->teacher)
                <div class="d-flex align-items-center mb-5">
                    <div class="symbol symbol-60px symbol-circle me-4">
                        @php
                            $imagePath = $group->teacher->photo ? (str_starts_with($group->teacher->photo, 'site/') ? asset($group->teacher->photo) : asset('storage/' . $group->teacher->photo)) : asset('assets/admin/media/avatars/blank.png');
                        @endphp
                        <img src="{{ $imagePath }}" alt="{{ $group->teacher->name }}" />
                    </div>
                    <div class="d-flex justify-content-start flex-column">
                        <span class="text-gray-800 fw-bold fs-5">{{ $group->teacher->name }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ $group->teacher->email }}</span>
                        <span class="text-muted fw-semibold fs-7">{{ $group->teacher->phone }}</span>
                    </div>
                </div>
            @else
                <span class="text-muted">لا يوجد مدرس</span>
            @endif
        </div>
        <div class="col-md-6">
            <h4 class="mb-4">البرنامج والمادة</h4>
            @if($group->subject)
                <div class="d-flex align-items-center mb-5">
                    <div class="symbol symbol-60px me-4">
                        @php
                            $subjectImage = $group->subject->image ? (str_starts_with($group->subject->image, 'site/') ? asset($group->subject->image) : asset('storage/' . $group->subject->image)) : asset('site/images/img/logo_backup.png');
                        @endphp
                        <img src="{{ $subjectImage }}" alt="{{ $group->subject->name }}" />
                    </div>
                    <div class="d-flex justify-content-start flex-column">
                        <span class="text-gray-800 fw-bold fs-5">{{ $group->subject->name }}</span>
                        <span class="text-muted fw-semibold fs-7">
                            البرنامج: {{ $group->subject->program ? $group->subject->program->name : 'غير محدد' }}
                        </span>
                        <span class="text-muted fw-semibold fs-7">
                            الرسوم: {{ $group->subject->fee }} {{ \App\Models\SiteSetting::current()->options['currency'] ?? 'JOD' }}
                        </span>
                    </div>
                </div>
            @else
                <span class="text-muted">لا توجد تفاصيل</span>
            @endif
        </div>
    </div>
    
    <div class="separator my-5"></div>
    
    <div class="row">
        <div class="col-md-12">
            <h4 class="mb-4">تفاصيل المحاضرات</h4>
            @php
                $dayNames = ['sat' => 'السبت', 'sun' => 'الأحد', 'mon' => 'الاثنين', 'tue' => 'الثلاثاء', 'wed' => 'الأربعاء', 'thu' => 'الخميس', 'fri' => 'الجمعة'];
                $groupDays = is_array($group->days) ? $group->days : json_decode($group->days, true);
                $mappedDays = collect($groupDays)->map(fn($day) => $dayNames[$day] ?? $day)->implode('، ');
            @endphp
            <div class="d-flex flex-column">
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-calendar-event fs-3 text-primary me-2"></i>
                    <span class="fs-6 fw-semibold text-gray-800">الأيام: </span>
                    <span class="fs-6 text-gray-600 ms-2">{{ $mappedDays ?: 'غير محدد' }}</span>
                </div>
                <div class="d-flex align-items-center mb-2">
                    <i class="bi bi-clock fs-3 text-primary me-2"></i>
                    <span class="fs-6 fw-semibold text-gray-800">الوقت: </span>
                    <span class="fs-6 text-gray-600 ms-2">
                        {{ $group->start_time ? \Carbon\Carbon::parse($group->start_time)->format('h:i A') : 'غير محدد' }}
                        - 
                        {{ $group->end_time ? \Carbon\Carbon::parse($group->end_time)->format('h:i A') : 'غير محدد' }}
                    </span>
                </div>
                <div class="d-flex align-items-center">
                    <i class="bi bi-people fs-3 text-primary me-2"></i>
                    <span class="fs-6 fw-semibold text-gray-800">السعة المتبقية: </span>
                    <span class="fs-6 text-gray-600 ms-2">
                        {{ max(0, $group->max_capacity - $group->registrations()->count()) }} / {{ $group->max_capacity }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-light" data-bs-dismiss="modal">إغلاق</button>
</div>
