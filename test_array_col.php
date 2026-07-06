<?php
$obj = new stdClass();
$obj->permission_id = 5;
$obj2 = new stdClass();
$obj2->permission_id = 10;

$arr = [$obj, $obj2];
print_r(array_column($arr, 'permission_id'));
