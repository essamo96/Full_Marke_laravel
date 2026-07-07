<?php

$controllers = glob(__DIR__ . '/app/Http/Controllers/Admin/*.php');
$requests = glob(__DIR__ . '/app/Http/Requests/Admin/*.php');

foreach ($controllers as $file) {
    $c = file_get_contents($file);
    
    // Remove $emp_id = Auth::guard('admin')->user()...company_id;
    $c = preg_replace('/\$emp_id\s*=\s*Auth::guard\([^\)]+\)->user\(\)->.*?;/is', '', $c);
    
    // Remove parent::$data['company_id'] = ... ;
    $c = preg_replace('/parent::\$data\[\'company_id\'\]\s*=\s*.*?;/is', '', $c);
    
    // Remove ->addColumn('company_id', function... { return $row->company->translation->name ?? ''; })
    $c = preg_replace('/->addColumn\(\'company_id\',[^;]+?return\s*\$row->company.*?\}\)/is', '', $c);
    $c = preg_replace('/->addColumn\(\'company_id\',\s*function\s*\(\$row\)\s*\{\s*return[^}]+\}\)/is', '', $c);

    // Remove 'company_id' from rawColumns(['status', 'actions', 'company_id'])
    $c = preg_replace('/(\'|")company_id(\'|")\s*,?\s*/', '', $c);
    
    // Remove 'company_id' => $request->company_id, etc.
    $c = preg_replace('/\'company_id\'\s*=>\s*[^,]+,/', '', $c);
    
    // In getSearch($request->name, $request->company_id, $emp_id) -> getSearch($request->name, null, 0)
    $c = str_replace('$request->company_id', 'null', $c);
    $c = str_replace('$emp_id', '0', $c);

    file_put_contents($file, $c);
}

foreach ($requests as $file) {
    $c = file_get_contents($file);
    // Remove 'company_id' => 'nullable|integer|exists:companies,id',
    $c = preg_replace('/\'company_id\'\s*=>\s*[^,]+,/', '', $c);
    file_put_contents($file, $c);
}

echo "Done.\n";
