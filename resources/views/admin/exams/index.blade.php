@extends('admin.layout.mainLayouts.master')

@section('title', __('app.exams'))

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('exams.view') }}" class="text-muted text-hover-primary">@lang('app.exams')</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection

@section('page-content')
<div class="card card-flush mt-5">
    <div class="card-header align-items-center py-5 gap-2 gap-md-5">
        <div class="card-toolbar">
            <a href="{{ route('exams.create') }}" class="btn btn-primary">
                <i class="ki-duotone ki-plus fs-2"></i>إضافة امتحان جديد
            </a>
        </div>
    </div>
    <div class="card-body pt-0">
        <div class="table-responsive">
            <table class="table align-middle table-row-dashed fs-6 gy-5">
                <thead>
                    <tr class="text-start text-gray-500 fw-bold fs-7 text-uppercase gs-0">
                        <th class="w-10px pe-2">#</th>
                        <th class="min-w-125px">عنوان الامتحان</th>
                        <th class="min-w-125px">المادة / المجموعة</th>
                        <th class="min-w-125px">المواعيد</th>
                        <th class="min-w-125px">الحالة</th>
                        <th class="text-end min-w-70px">إجراءات</th>
                    </tr>
                </thead>
                <tbody class="fw-semibold text-gray-600">
                    @forelse($exams as $exam)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $exam->title }}</td>
                        <td>
                            <div class="d-flex flex-column">
                                <span class="text-gray-800">{{ $exam->subject->name ?? '-' }}</span>
                                <span class="text-muted fs-7">{{ $exam->group->name ?? '-' }}</span>
                            </div>
                        </td>
                        <td>
                            @if($exam->start_time && $exam->end_time)
                                <div><span class="badge badge-light-success mb-1">يبدأ: {{ $exam->start_time->format('Y-m-d H:i') }}</span></div>
                                <div><span class="badge badge-light-danger">ينتهي: {{ $exam->end_time->format('Y-m-d H:i') }}</span></div>
                            @else
                                <span class="badge badge-light-secondary">غير محدد</span>
                            @endif
                        </td>
                        <td>
                            @if($exam->status === 'published')
                                <span class="badge badge-light-success">منشور</span>
                            @elseif($exam->status === 'completed')
                                <span class="badge badge-light-primary">مكتمل</span>
                            @else
                                <span class="badge badge-light-warning">مسودة</span>
                            @endif
                        </td>
                        <td class="text-end">
                            <a href="{{ route('exams.results', $exam) }}" class="btn btn-icon btn-bg-light btn-active-color-info btn-sm me-1" title="نتائج الامتحان">
                                <i class="ki-duotone ki-chart-simple fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span></i>
                            </a>
                            @if($exam->allowsGuests())
                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-warning btn-sm me-1" title="نسخ رابط الضيوف" onclick="copyGuestLink(this)" data-link="{{ route('guest.exam.enter', $exam) }}">
                                    <i class="ki-duotone ki-copy fs-2"><span class="path1"></span><span class="path2"></span></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1 btn-show-qr" title="QR Code"
                                        data-url="{{ route('qr.exam', $exam) }}" data-name="{{ $exam->title }}">
                                    <i class="bi bi-qr-code fs-5"></i>
                                </button>
                            @endif
                            <a href="{{ route('exams.edit', $exam) }}" class="btn btn-icon btn-bg-light btn-active-color-primary btn-sm me-1" title="تعديل">
                                <i class="ki-duotone ki-pencil fs-2"><span class="path1"></span><span class="path2"></span></i>
                            </a>
                            <form action="{{ route('exams.destroy', $exam) }}" method="POST" class="d-inline-block">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="btn btn-icon btn-bg-light btn-active-color-danger btn-sm" onclick="return confirm('هل أنت متأكد من الحذف؟')">
                                    <i class="ki-duotone ki-trash fs-2"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center text-muted py-5">لا يوجد امتحانات مضافة.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">
            {{ $exams->links() }}
        </div>
    </div>
</div>

{{-- QR Code preview modal --}}
<div class="modal fade" id="qr_modal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-400px">
        <div class="modal-content text-center">
            <div class="modal-header">
                <h2 class="fw-bold" id="qr_modal_title">QR Code</h2>
                <div class="btn btn-icon btn-sm btn-active-icon-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body py-10">
                <img id="qr_modal_img" src="" alt="QR Code" class="w-100" style="max-width: 300px;">
                <a id="qr_modal_download" href="" download class="btn btn-primary d-block mt-6 mx-auto" style="max-width: 200px;">
                    <i class="bi bi-download me-1"></i> تحميل
                </a>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    function copyGuestLink(btn) {
        const link = btn.getAttribute('data-link');
        navigator.clipboard.writeText(link).then(() => {
            btn.classList.add('btn-active-color-success');
            setTimeout(() => btn.classList.remove('btn-active-color-success'), 1500);
        });
    }

    document.addEventListener('click', function (e) {
        const btn = e.target.closest('.btn-show-qr');
        if (!btn) return;
        const url = btn.dataset.url;
        const name = btn.dataset.name;
        document.getElementById('qr_modal_title').textContent = name;
        document.getElementById('qr_modal_img').src = url + '?t=' + Date.now();
        document.getElementById('qr_modal_download').setAttribute('href', url);
        document.getElementById('qr_modal_download').setAttribute('download', name.replace(/\s+/g, '_') + '_qr.svg');
        var modal = new bootstrap.Modal(document.getElementById('qr_modal'));
        modal.show();
    });
</script>
@endpush
