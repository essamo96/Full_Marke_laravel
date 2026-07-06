<?php
$f = 'c:\laragon\www\full-mark-academy\resources\views\admin\dashboard\view.blade.php';
$c = file_get_contents($f);
$c = str_replace('brand-logos/laravel-2.svg', 'brand-logos/google-icon.svg', $c);
$c = str_replace('brand-logos/vue-9.svg', 'brand-logos/facebook-1.svg', $c);
$c = str_replace('brand-logos/bootstrap5.svg', 'brand-logos/instagram-2016.svg', $c);
$c = str_replace('brand-logos/angular-icon.svg', 'brand-logos/youtube.svg', $c);
$c = str_replace('brand-logos/spring-3.svg', 'brand-logos/linkedin-1.svg', $c);
$c = str_replace('brand-logos/typescript-1.svg', 'brand-logos/telegram.svg', $c);
$c = str_replace('illustrations/sigma-1/17-dark.png', 'svg/files/blank-image.svg', $c);
$c = str_replace('svg/shapes/top-green.png', 'svg/files/blank-image.svg', $c);
file_put_contents($f, $c);
echo "Done";
