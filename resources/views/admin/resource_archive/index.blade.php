@extends('admin.layout.mainLayouts.master')

@section('title', 'أرشيف المرفقات')

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route('resource-archive.view') }}" class="text-muted text-hover-primary">أرشيف المرفقات</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-dark">سلة المهملات للموارد</li>
@endsection

@section('page-content')

<div class="row g-5 g-xl-10">
    <div class="col-xl-12 mb-5 mb-xl-10">
        <!-- Content Card -->
        <div class="card card-flush h-xl-100">
            <div class="card-header pt-7">
                <h3 class="card-title align-items-start flex-column">
                    <span class="card-label fw-bold text-gray-800">أرشيف المرفقات المحذوفة</span>
                    <span class="text-gray-400 mt-1 fw-semibold fs-6">
                        يمكنك استعادة المرفقات المحذوفة أو تدميرها بشكل نهائي من السيرفر.
                    </span>
                </h3>
            </div>
            <div class="card-body pt-5">

                @if(session('success_message'))
                    <div class="alert alert-success d-flex align-items-center p-5 mb-10">
                        <i class="ki-duotone ki-shield-tick fs-2hx text-success me-4"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-success">تم بنجاح</h4>
                            <span>{{ session('success_message') }}</span>
                        </div>
                    </div>
                @endif
                @if(session('danger'))
                    <div class="alert alert-danger d-flex align-items-center p-5 mb-10">
                        <i class="ki-duotone ki-information-5 fs-2hx text-danger me-4"><span class="path1"></span><span class="path2"></span></i>
                        <div class="d-flex flex-column">
                            <h4 class="mb-1 text-danger">تنبيه</h4>
                            <span>{{ session('danger') }}</span>
                        </div>
                    </div>
                @endif
                
                @if($resources->isEmpty())
                    <div class="d-flex flex-column flex-center text-center p-10">
                        <i class="ki-duotone ki-trash fs-5x text-muted mb-5"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i>
                        <div class="fs-4 fw-bold text-gray-900 mb-2">الأرشيف فارغ</div>
                        <div class="fs-6 text-gray-500">لا توجد أي مرفقات أو فيديوهات محذوفة حالياً.</div>
                    </div>
                @else
                    <div class="table-responsive">
                        <table class="table table-row-dashed align-middle gs-0 gy-4">
                            <thead>
                                <tr class="fw-bold text-muted bg-light">
                                    <th class="ps-4 min-w-200px rounded-start">عنوان المرفق</th>
                                    <th class="min-w-125px">المادة / الدرس</th>
                                    <th class="min-w-125px">تاريخ الحذف</th>
                                    <th class="min-w-150px">حُذف بواسطة</th>
                                    <th class="min-w-200px text-end pe-4 rounded-end">الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($resources as $resource)
                                    @php
                                        $resourceType = $resource->type ?? 'link';
                                        $iconMap = [
                                            'video' => 'ki-youtube',
                                            'document' => 'ki-file',
                                            'image' => 'ki-picture',
                                            'link' => 'ki-link',
                                            'zoom' => 'ki-monitor-mobile',
                                        ];
                                        $icon = $iconMap[$resourceType] ?? 'ki-link';
                                        $title = match($resourceType) {
                                            'video' => 'فيديو',
                                            'document' => 'ملف',
                                            'image' => 'صورة',
                                            'zoom' => 'Zoom',
                                            default => 'رابط',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="ps-4">
                                            <div class="d-flex align-items-center">
                                                <div class="symbol symbol-50px me-3">
                                                    <div class="symbol-label bg-light-danger">
                                                        <i class="ki-duotone {{ $icon }} fs-1 text-danger"><span class="path1"></span><span class="path2"></span></i>
                                                    </div>
                                                </div>
                                                <div class="d-flex justify-content-start flex-column">
                                                    <a href="#" class="text-gray-800 fw-bold text-hover-primary mb-1 fs-6">{{ $resource->title }}</a>
                                                    <span class="text-gray-400 fw-semibold d-block fs-7">{{ $title }}</span>
                                                </div>
                                            </div>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6">{{ $resource->subject->name_ar ?? $resource->subject->name ?? 'غير معروف' }}</span>
                                            <span class="text-gray-400 fw-semibold d-block fs-7">{{ $resource->lesson->name_ar ?? $resource->lesson->name_en ?? 'بدون درس' }}</span>
                                        </td>
                                        <td>
                                            <span class="text-gray-800 fw-bold d-block fs-6">{{ $resource->deleted_at->format('Y-m-d') }}</span>
                                            <span class="text-gray-400 fw-semibold d-block fs-7">{{ $resource->deleted_at->format('h:i A') }}</span>
                                        </td>
                                        <td>
                                            @if($resource->deletedBy)
                                                <div class="d-flex align-items-center">
                                                    <div class="d-flex justify-content-start flex-column">
                                                        <span class="text-gray-800 fw-bold mb-1 fs-6">{{ $resource->deletedBy->name }}</span>
                                                        <span class="text-gray-400 fw-semibold d-block fs-7">{{ $resource->deletedBy->email }}</span>
                                                    </div>
                                                </div>
                                            @else
                                                <span class="badge badge-light-warning">غير محدد (ربما بواسطة مدرس)</span>
                                            @endif
                                        </td>
                                        <td class="text-end pe-4">
                                            <div class="d-flex justify-content-end gap-2">
                                                <form action="{{ route('resource-archive.restore', $resource->id) }}" method="POST">
                                                    @csrf
                                                    <button type="submit" class="btn btn-sm btn-light-success fw-bold">
                                                        <i class="ki-duotone ki-arrows-loop fs-4 me-1"><span class="path1"></span><span class="path2"></span></i> استعادة
                                                    </button>
                                                </form>

                                                <form action="{{ route('resource-archive.force-delete', $resource->id) }}" method="POST" onsubmit="return confirm('هل أنت متأكد؟ سيتم تدمير الملف من السيرفر نهائياً ولا يمكن استرجاعه.');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-light-danger fw-bold">
                                                        <i class="ki-duotone ki-trash fs-4 me-1"><span class="path1"></span><span class="path2"></span><span class="path3"></span><span class="path4"></span><span class="path5"></span></i> تدمير نهائي
                                                    </button>
                                                </form>
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        </div>
    </div>
</div>

@endsection
