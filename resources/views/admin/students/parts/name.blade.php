<div class="d-flex align-items-center">
    <div class="symbol symbol-circle symbol-50px overflow-hidden me-3">
        <a href="{{ url('admin/students/show/' . $student->id) }}">
            @if($student->image)
                <div class="symbol-label">
                    <img src="{{ asset('storage/' . $student->image) }}" alt="{{ $student->full_name_ar }}" class="w-100" />
                </div>
            @else
                <div class="symbol-label fs-3 bg-light-primary text-primary">
                    {{ mb_substr($student->full_name_ar, 0, 1) }}
                </div>
            @endif
        </a>
    </div>
    <div class="d-flex flex-column">
        <a href="{{ url('admin/students/show/' . $student->id) }}" class="text-gray-800 text-hover-primary mb-1">{{ $student->full_name_ar }}</a>
        <span>{{ $student->email ?? $student->phone ?? 'لا يوجد بريد إلكتروني' }}</span>
    </div>
</div>
