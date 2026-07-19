@extends('layouts.student')

@section('title', 'سجل الحضور والغياب')
@section('page_title_en', 'Attendance')
@section('page_title_ar', 'سجل الحضور والغياب')

@section('content')
<div class="fade-in-up">
    <div class="mb-5 d-flex justify-content-between align-items-center">
        <h2 class="fw-bold mb-0 text-white" data-en="My Attendance" data-ar="سجل حضوري">سجل حضوري</h2>
    </div>

    <div class="glass-panel rounded-4 p-0 overflow-hidden">
        <div class="table-responsive">
            <table class="table table-borderless text-white mb-0 align-middle">
                <thead class="bg-dark bg-opacity-50">
                    <tr>
                        <th class="py-3 px-4 fw-bold">التاريخ</th>
                        <th class="py-3 px-4 fw-bold">المجموعة</th>
                        <th class="py-3 px-4 fw-bold text-center">الحالة</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($attendances as $attendance)
                        <tr class="border-bottom border-white border-opacity-10 hover-bg-white-5 transition-all">
                            <td class="py-3 px-4">
                                {{ \Carbon\Carbon::parse($attendance->date)->format('Y-m-d') }}
                            </td>
                            <td class="py-3 px-4 opacity-75">
                                {{ $attendance->group->name ?? 'غير محدد' }}
                            </td>
                            <td class="py-3 px-4 text-center">
                                @if($attendance->status == 'present')
                                    <span class="badge bg-success bg-opacity-25 text-success border border-success px-3 py-2 rounded-pill">حاضر</span>
                                @elseif($attendance->status == 'absent')
                                    <span class="badge bg-danger bg-opacity-25 text-danger border border-danger px-3 py-2 rounded-pill">غائب</span>
                                @elseif($attendance->status == 'late')
                                    <span class="badge bg-warning bg-opacity-25 text-warning border border-warning px-3 py-2 rounded-pill">متأخر</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-25 text-white border border-secondary px-3 py-2 rounded-pill">{{ $attendance->status }}</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="text-center py-5 opacity-50">
                                <i class="bi bi-calendar-x fs-2 d-block mb-2"></i>
                                لا يوجد سجل حضور متاح حالياً.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($attendances->hasPages())
        <div class="mt-4 d-flex justify-content-center">
            {{ $attendances->links('pagination::bootstrap-5') }}
        </div>
    @endif
</div>

@push('styles')
<style>
    .hover-bg-white-5:hover {
        background-color: rgba(255, 255, 255, 0.05);
    }
</style>
@endpush
@endsection
