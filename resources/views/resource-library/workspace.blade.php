{{--
    Resource library workspace - shared by the admin and the teacher screens.

    Expects:
      $role    'admin' | 'teacher'
      $mode    'manager' (units > lessons > resources of one subject) | 'library' (every resource, one table)
      $config  array handed to the JavaScript (api base, csrf, subject / tree, ...)

    The screen is rendered by public/assets/js/resource-library/*.js from the JSON API
    (routes/library-api.php). Nothing here is role specific except where the scripts come from.
--}}
@php
    $role = $role ?? 'teacher';
    $mode = $mode ?? 'manager';
    $jsBase = 'assets/js/resource-library/';
@endphp

@push('styles')
    <link rel="stylesheet" href="{{ asset_ver('assets/css/resource-library.css') }}">
    @if($role === 'teacher')
        <link rel="stylesheet" href="https://cdn.datatables.net/1.13.8/css/jquery.dataTables.min.css">
    @endif
@endpush

<div class="rl-root rl-theme-{{ $role }}" id="rl-app" dir="rtl" data-mode="{{ $mode }}">
    <div class="rl-skeleton"></div>
    <div class="rl-skeleton"></div>
    <div class="rl-skeleton"></div>
</div>

@push('scripts')
    @if($role === 'teacher')
        {{-- the teacher layout ships no jQuery / DataTables; the admin (Metronic) bundle already has both --}}
        <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
        <script src="https://cdn.datatables.net/1.13.8/js/jquery.dataTables.min.js"></script>
    @endif
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.2/Sortable.min.js"></script>
    <script src="{{ asset('assets/vendor/resumable/resumable.js') }}"></script>
    <script src="{{ asset('assets/js/secure-watermark.js') }}"></script>

    <script>window.RL_CONFIG = @json($config);</script>
    <script src="{{ asset_ver($jsBase.'rl-core.js') }}"></script>
    <script src="{{ asset_ver($jsBase.'rl-panels.js') }}"></script>
    <script src="{{ asset_ver($jsBase.'rl-forms.js') }}"></script>
    <script src="{{ asset_ver($jsBase.($mode === 'library' ? 'rl-library.js' : 'rl-manager.js')) }}"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var root = document.getElementById('rl-app');
            if (!window.RL) { root.innerHTML = '<div class="alert alert-danger">تعذّر تحميل واجهة المكتبة.</div>'; return; }
            @if($mode === 'library')
                RL.Library.mount(root);
            @else
                RL.Manager.mount(root);
            @endif
        });
    </script>
@endpush
