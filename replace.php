<?php
$files = ['temp_header.html', 'temp_sidebar.html', 'temp_aside.html', 'temp_content.html'];
foreach ($files as $f) {
    if (!file_exists($f)) continue;
    $c = file_get_contents($f);
    // Replace "assets/..." or 'assets/...' with "{{ asset('assets/admin/') }}/..."
    $c = preg_replace('/(["\'])assets\/([^"\']+)\1/', '$1{{ asset(\'assets/admin/$2\') }}$1', $c);
    file_put_contents($f, $c);
}
echo "Done";
