<?php

$addFiles = glob(__DIR__ . '/resources/views/admin/*/add.blade.php');
$viewFiles = glob(__DIR__ . '/resources/views/admin/*/view.blade.php');

$addBreadcrumb = <<<EOF
@section('title')
    @lang('app.' . \$active_menu) - {{ isset(\$info) && \$info->id ? __('app.edit') : __('app.add') }}
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route(\$active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . \$active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">{{ isset(\$info) && \$info->id ? __('app.edit') : __('app.add') }}</li>
@endsection
EOF;

$viewBreadcrumb = <<<EOF
@section('title')
    @lang('app.' . \$active_menu)
@stop

@section('breadcrumb')
    <li class="breadcrumb-item text-muted">
        <a href="{{ route(\$active_menu . '.view') }}" class="text-muted text-hover-primary">@lang('app.' . \$active_menu)</a>
    </li>
    <li class="breadcrumb-item">
        <span class="bullet bg-gray-400 w-5px h-2px"></span>
    </li>
    <li class="breadcrumb-item text-muted">@lang('app.view')</li>
@endsection
EOF;

foreach ($addFiles as $file) {
    $c = file_get_contents($file);
    
    // Replace @section('title') ... @stop with new title + breadcrumb
    $c = preg_replace('/@section\(\'title\'\).*?@stop/is', $addBreadcrumb, $c);
    
    // Remove company_id hidden inputs
    $c = preg_replace('/<input[^>]+name=[\'"]company_id[\'"][^>]*>/is', '', $c);
    
    file_put_contents($file, $c);
}

foreach ($viewFiles as $file) {
    $c = file_get_contents($file);
    
    // Check if it already has breadcrumb
    if (strpos($c, "@section('breadcrumb')") === false) {
        $c = preg_replace('/@section\(\'title\'\).*?@stop/is', $viewBreadcrumb, $c);
    } else {
        // Just replace title
        $c = preg_replace('/@section\(\'title\'\).*?@stop/is', "@section('title')\n    @lang('app.' . \$active_menu)\n@stop", $c);
    }
    
    file_put_contents($file, $c);
}

echo "Done replacing titles and company_id in views.\n";
