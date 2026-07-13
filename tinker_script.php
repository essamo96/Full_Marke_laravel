<?php
$req = Request::create('http://localhost:8000/admin/dashboard', 'GET');
Auth::guard('admin')->loginUsingId(1);
$res = app()->handle($req);
file_put_contents('dashboard.html', $res->getContent());
