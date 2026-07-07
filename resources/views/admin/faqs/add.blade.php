@extends('admin.layout.mainLayouts.master')
@section('title')
    @lang('app.' . $active_menu) - {{ isset($info) && $info->id ? __('app.edit') : __('app.add') }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route($active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . $active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ isset($info) && $info->id ? __('app.edit') : __('app.add') }}</li>
@endsection

@section('page-content')
    <div class="card">
        <div class="card-body py-4">
            @include('admin.layout.masterLayouts.error')

            <form action="" method="POST">
                <div class="row justify-content-center">
                    <div class="col-9">

                        {{-- Tabs Navigation --}}
                        <ul class="nav nav-tabs nav-line-tabs nav-line-tabs-2x mb-5 fs-5 fw-bold" id="pageTab" role="tablist">
                            <li class="nav-item me-3" role="presentation">
                                <button class="nav-link active d-flex align-items-center text-active-primary pb-4" id="basic-tab" data-bs-toggle="tab" data-bs-target="#basic" type="button" role="tab">
                                    <i class="bi bi-gear-fill fs-2 me-2"></i> {{ \App\Helpers\translate('basic_settings') }}
                                </button>
                            </li>

                            @foreach ($languages as $lang)
                                <li class="nav-item me-3" role="presentation">
                                    <button class="nav-link d-flex align-items-center text-active-success pb-4" id="lang-{{ $lang->prefix }}-tab" data-bs-toggle="tab"
                                            data-bs-target="#lang-{{ $lang->prefix }}" type="button" role="tab">
                                        <i class="bi bi-globe fs-2 me-2"></i> {{ $lang->name }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Tabs Content --}}
                        <div class="tab-content mt-5" id="pageTabContent">

                            {{-- Basic Tab --}}
                            <div class="tab-pane fade show active" id="basic" role="tabpanel">
                                <div class="row mb-5">
                                
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="required fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('sort') }}</label>
                                        <input type="number" class="form-control form-control-solid" name="sort"
                                            value="{{ $info ? $info->sort : old('sort') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('link') }}</label>
                                        <input type="text" class="form-control form-control-solid" name="link"
                                            value="{{ $info ? $info->link : old('link') }}">
                                    </div>
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('icon') }}</label>
                                        <input type="text" class="form-control form-control-solid" name="icon"
                                            value="{{ $info ? $info->icon : old('icon') }}">
                                    </div>
                                </div>

                                <div class="row mb-5">
                                    <div class="col-md-6 fv-row fv-plugins-icon-container">
                                        <label class="p-2">{{ \App\Helpers\translate('status') }}</label>
                                        <label class="form-check form-switch">
                                            <?php $data = $info ? $info->status : old('status'); ?>
                                            <input class="form-check-input" name="status" type="checkbox" value="1"
                                                {{ $data == 1 ? 'checked' : '' }}>
                                        </label>
                                    </div>
                                </div>

                                {{-- Navigation Buttons --}}
                                <div class="text-center pt-2">
                                    <a href="{{ route($active_menu . '.view') }}"
                                        class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                    <button type="button"
                                        class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">{{ \App\Helpers\translate('next') }}</button>
                                </div>
                            </div>

                            {{-- Language Tabs --}}
                            @foreach ($languages as $lang)
                                @php
                                    $trans = $translations[$lang->prefix] ?? null;
                                @endphp
                                <div class="tab-pane fade" id="lang-{{ $lang->prefix }}" role="tabpanel">
                                    <div class="row mb-5">
                                        @if($info)
                                            <div class="row mb-5">
                                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                                    <label class=" fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('answer') }}
                                                        ({{ $lang->prefix }})
                                                    </label>
                                                    <input type="text" class="form-control form-control-solid"
                                                        name="{{ $lang->prefix }}[answer]"
                                                        value="{{ old($lang->prefix . '.answer', $trans?->answer ?? '') }}">
                                                </div>
                                                <div class="col-md-6 fv-row fv-plugins-icon-container">
                                                    <label class=" fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('question') }}
                                                        ({{ $lang->prefix }})</label>
                                                    <input type="text" class="form-control form-control-solid"
                                                        name="{{ $lang->prefix }}[question]"
                                                        value="{{ old($lang->prefix . '.question', $trans?->question ?? '') }}">
                                                </div>
                                            </div>
                                        @else
                                            @php
                                                $oldFaqs = old($lang->prefix . '_faqs', []);
                                            @endphp
                                            <div class="faq-repeater">
                                                <div data-repeater-list="{{ $lang->prefix }}_faqs">
                                                    @if(count($oldFaqs) > 0)
                                                        @foreach($oldFaqs as $oldFaq)
                                                        <div data-repeater-item class="row mb-5 align-items-center bg-light p-4 rounded">
                                                            <div class="col-md-5 fv-row">
                                                                <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('question') }} ({{ $lang->prefix }})</label>
                                                                <input type="text" class="form-control form-control-solid" name="question" placeholder="{{ \App\Helpers\translate('question') }}" value="{{ $oldFaq['question'] ?? '' }}">
                                                            </div>
                                                            <div class="col-md-6 fv-row">
                                                                <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('answer') }} ({{ $lang->prefix }})</label>
                                                                <input type="text" class="form-control form-control-solid" name="answer" placeholder="{{ \App\Helpers\translate('answer') }}" value="{{ $oldFaq['answer'] ?? '' }}">
                                                            </div>
                                                            <div class="col-md-1 text-end mt-7">
                                                                <button data-repeater-delete type="button" class="btn btn-sm btn-icon btn-light-danger">
                                                                    <i class="la la-trash fs-2"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                        @endforeach
                                                    @else
                                                        <div data-repeater-item class="row mb-5 align-items-center bg-light p-4 rounded">
                                                            <div class="col-md-5 fv-row">
                                                                <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('question') }} ({{ $lang->prefix }})</label>
                                                                <input type="text" class="form-control form-control-solid" name="question" placeholder="{{ \App\Helpers\translate('question') }}">
                                                            </div>
                                                            <div class="col-md-6 fv-row">
                                                                <label class="fs-5 fw-semibold mb-2 required">{{ \App\Helpers\translate('answer') }} ({{ $lang->prefix }})</label>
                                                                <input type="text" class="form-control form-control-solid" name="answer" placeholder="{{ \App\Helpers\translate('answer') }}">
                                                            </div>
                                                            <div class="col-md-1 text-end mt-7">
                                                                <button data-repeater-delete type="button" class="btn btn-sm btn-icon btn-light-danger">
                                                                    <i class="la la-trash fs-2"></i>
                                                                </button>
                                                            </div>
                                                        </div>
                                                    @endif
                                                </div>
                                                <div class="mt-3">
                                                    <button data-repeater-create type="button" class="btn btn-sm btn-light-primary">
                                                        <i class="la la-plus"></i> {{ \App\Helpers\translate('add_new') }}
                                                    </button>
                                                </div>
                                            </div>
                                        @endif
                                        <div class="row mb-5">
                                            <div class="col-md-12 fv-row fv-plugins-icon-container">
                                                <label class=" fs-5 fw-semibold mb-2">{{ \App\Helpers\translate('title') }}
                                                    ({{ $lang->prefix }})</label>
                                                <input type="text" class="form-control form-control-solid"
                                                    name="{{ $lang->prefix }}[title]"
                                                    value="{{ old($lang->prefix . '.title', $trans?->title ?? '') }}">
                                            </div>
                                        </div>
                                        <div class="row mb-5">
                                            <div class="form-floating mb-9 row">
                                                <div class="fv-row mb-10 col-12">
                                                    <label class="fw-semibold fs-6 mb-2"
                                                        for="description">{{ \App\Helpers\translate('description') }} ({{ $lang->prefix }})</label>
                                                    <textarea name="{{ $lang->prefix }}[description]" id="description" class="form-control form-control-solid">{{ old($lang->prefix . '.description', $trans?->description ?? '') }}</textarea>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="text-center pt-2">
                                            <a href="{{ route($active_menu . '.view') }}"
                                                class="btn btn-light btn-sm">{{ \App\Helpers\translate('cancel') }}</a>
                                            <button type="button"
                                                class="btn btn-outline btn-outline-dashed btn-outline-success btn-active-light-success ms-2 btn-sm prev-tab">{{ \App\Helpers\translate('previous') }}</button>

                                            @if ($loop->last)
                                                <button type="submit"
                                                    class="btn btn-primary ms-2 btn-sm">{{ \App\Helpers\translate('save') }}</button>
                                            @else
                                                <button type="button"
                                                    class="btn btn-outline btn-outline-dashed btn-outline-primary btn-active-light-primary ms-2 btn-sm next-tab">{{ \App\Helpers\translate('next') }}</button>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            @endforeach

                        </div>
                    </div>
                </div>
                {{ csrf_field() }}
            </form>
        </div>
    </div>
@stop

@section('js')
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.repeater/1.2.1/jquery.repeater.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Tagify init (if any)
            if (document.querySelector("#kt_tagify_4")) {
                new Tagify(document.querySelector("#kt_tagify_4"));
            }

            const tabButtons = Array.from(document.querySelectorAll('#pageTab button'));

            // Next button
            document.querySelectorAll('.next-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const activeIndex = tabButtons.findIndex(tab => tab.classList.contains('active'));
                    if (activeIndex < tabButtons.length - 1) new bootstrap.Tab(tabButtons[activeIndex + 1]).show();
                });
            });

            // Previous button
            document.querySelectorAll('.prev-tab').forEach(btn => {
                btn.addEventListener('click', function() {
                    const activeIndex = tabButtons.findIndex(tab => tab.classList.contains('active'));
                    if (activeIndex > 0) new bootstrap.Tab(tabButtons[activeIndex - 1]).show();
                });
            });

            // Initialize repeater for FAQs
            $('.faq-repeater').repeater({
                initEmpty: false,
                defaultValues: {
                    'question': '',
                    'answer': ''
                },
                show: function () {
                    $(this).slideDown();
                },
                hide: function (deleteElement) {
                    $(this).slideUp(deleteElement);
                }
            });
        });
    </script>
@stop
