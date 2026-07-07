@extends('admin.layout.mainLayouts.master')

@section('title')
    عرض الرسالة
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">عرض الرسالة</li>
@endsection

@section('page-content')
    <div class="app-main flex-column flex-row-fluid" id="kt_app_main">
        <div id="kt_app_content" class="app-content flex-column-fluid">
            <div id="kt_app_content_container" class="app-container container-xxl">
                <div class="card card-flush py-4">
                    <div class="card-header">
                        <div class="card-title">
                            <h2>تفاصيل الرسالة</h2>
                        </div>
                    </div>
                    <div class="card-body pt-0">
                        <div class="mb-5 row">
                            <label class="col-sm-3 fw-bold text-muted">الاسم:</label>
                            <div class="col-sm-9">{{ $contact->name ?? '-' }}</div>
                        </div>
                        <div class="mb-5 row">
                            <label class="col-sm-3 fw-bold text-muted">البريد الإلكتروني:</label>
                            <div class="col-sm-9">{{ $contact->email ?? '-' }}</div>
                        </div>
                        <div class="mb-5 row">
                            <label class="col-sm-3 fw-bold text-muted">رقم الجوال:</label>
                            <div class="col-sm-9" dir="ltr" style="text-align: right;">{{ $contact->phone ?? '-' }}</div>
                        </div>
                        <div class="mb-5 row">
                            <label class="col-sm-3 fw-bold text-muted">تاريخ الارسال:</label>
                            <div class="col-sm-9">{{ $contact->created_at ? $contact->created_at->format('Y-m-d H:i') : '-' }}</div>
                        </div>
                        <div class="mb-5 row">
                            <label class="col-sm-3 fw-bold text-muted">الموضوع:</label>
                            <div class="col-sm-9 fw-bold">{{ $contact->subject ?? '-' }}</div>
                        </div>
                        <div class="mb-5 row">
                            <label class="col-sm-3 fw-bold text-muted">الرسالة:</label>
                            <div class="col-sm-9 bg-light p-5 rounded">
                                {{ $contact->message ?? '-' }}
                            </div>
                        </div>

                        <div class="mt-10 d-flex justify-content-end gap-3">
                            @if(!empty($contact->phone))
                                @php
                                    $waNumber = preg_replace('/[^0-9]/', '', $contact->phone);
                                    if(strlen($waNumber) > 8) {
                                        // Simple heuristic for WhatsApp: Ensure it has country code, or at least it's a number
                                    }
                                @endphp
                                <a href="https://wa.me/{{ $waNumber }}" target="_blank" class="btn btn-success">
                                    <i class="bi bi-whatsapp"></i> رد عبر واتساب
                                </a>
                            @endif
                            @if(!empty($contact->email))
                                <a href="mailto:{{ $contact->email }}" class="btn btn-info">
                                    <i class="bi bi-envelope"></i> رد عبر الإيميل
                                </a>
                            @endif
                            <a href="{{ route('contacts.view') }}" class="btn btn-primary">رجوع للرسائل</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop
