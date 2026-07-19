<div class="modal fade" id="kt_modal_invoices" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered mw-800px">
        <div class="modal-content rounded">
            <div class="modal-header pb-0 border-0 justify-content-end">
                <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <i class="ki-duotone ki-cross fs-1"><span class="path1"></span><span class="path2"></span></i>
                </div>
            </div>
            <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                <div class="mb-13 text-center">
                    <h1 class="mb-3">الفواتير والدفعات المالية</h1>
                    <div class="text-muted fw-semibold fs-5">الطالب: <span class="text-primary">{{ $student->full_name_ar }}</span></div>
                </div>

                <div class="table-responsive">
                    <table class="table align-middle table-row-dashed fs-6 gy-5">
                        <thead>
                            <tr class="text-start text-gray-400 fw-bold fs-7 text-uppercase gs-0">
                                <th>#</th>
                                <th>المادة / المجموعة</th>
                                <th>الإجمالي</th>
                                <th>المدفوع</th>
                                <th>المتبقي</th>
                                <th>حالة الدفع</th>
                            </tr>
                        </thead>
                        <tbody class="fw-semibold text-gray-600">
                            @forelse($student->registrations as $reg)
                                @php
                                    $remaining = $reg->fee_snapshot - $reg->amount_paid;
                                @endphp
                                <tr>
                                    <td>{{ $loop->iteration }}</td>
                                    <td>
                                        <div class="d-flex flex-column">
                                            <span class="text-gray-800 mb-1">{{ $reg->subject->name ?? '-' }}</span>
                                            <span class="text-muted fs-7">{{ $reg->group->name ?? '-' }}</span>
                                        </div>
                                    </td>
                                    <td>{{ number_format($reg->fee_snapshot, 2) }}</td>
                                    <td class="text-success">{{ number_format($reg->amount_paid, 2) }}</td>
                                    <td class="{{ $remaining > 0 ? 'text-danger' : 'text-success' }}">{{ number_format($remaining, 2) }}</td>
                                    <td>
                                        @if($remaining <= 0)
                                            <span class="badge badge-light-success fw-bold">مدفوع بالكامل</span>
                                        @elseif($reg->amount_paid > 0)
                                            <span class="badge badge-light-warning fw-bold">مدفوع جزئياً</span>
                                        @else
                                            <span class="badge badge-light-danger fw-bold">غير مدفوع</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">لا يوجد فواتير مالية مسجلة لهذا الطالب</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

            </div>
        </div>
    </div>
</div>
