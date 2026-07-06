<!DOCTYPE html>
<html lang="en">
<head>
    <base href="../../../"/>
    <title>Metronic Login</title>
    <meta charset="utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="admin/assets/media/logos/favicon.ico" />
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <link href="admin/assets/plugins/global/plugins.bundle.css" rel="stylesheet" type="text/css" />
    <link href="admin/assets/css/style.bundle.css" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center">
    <script>
        var defaultThemeMode = "light";
        var themeMode;
        if (document.documentElement) {
            if (document.documentElement.hasAttribute("data-bs-theme-mode")) {
                themeMode = document.documentElement.getAttribute("data-bs-theme-mode");
            } else {
                if (localStorage.getItem("data-bs-theme") !== null) {
                    themeMode = localStorage.getItem("data-bs-theme");
                } else {
                    themeMode = defaultThemeMode;
                }
            }
            if (themeMode === "system") {
                themeMode = window.matchMedia("(prefers-color-scheme: dark)").matches ? "dark" : "light";
            }
            document.documentElement.setAttribute("data-bs-theme", themeMode);
        }
    </script>

    <div class="d-flex flex-column flex-root" id="kt_app_root">
        <style>
            body {
                background-image: url('admin/assets/media/auth/bg10.jpeg');
            }

            [data-bs-theme="dark"] body {
                background-image: url('admin/assets/media/auth/bg10-dark.jpeg');
            }
        </style>

        {{ $slot }}

    </div>

    <script>var hostUrl = "assets/";</script>
    <script src="{{asset('admin/assets/plugins/global/plugins.bundle.js')}}"></script>
    <script src="{{asset('admin/assets/js/scripts.bundle.js')}}"></script>
    @yield('js')

</body>
</html>

{{-- <!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <base href="{{ asset('') }}">
    <title>تسجيل الدخول - النظام</title>
    <meta charset="utf-8" />
    <meta name="description" content="Metronic - Bootstrap Admin Template" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <link rel="shortcut icon" href="{{ asset('admin/assets/media/logos/favicon.ico') }}" />
    <!-- الخطوط -->
    <link rel="stylesheet" href="https://fonts.googleapis.com/css?family=Inter:300,400,500,600,700" />
    <!-- ملفات CSS الأساسية -->
    <link href="{{ asset('admin/assets/plugins/global/plugins.bundle.css') }}" rel="stylesheet" type="text/css" />
    <link href="{{ asset('admin/assets/css/style.bundle.css') }}" rel="stylesheet" type="text/css" />
</head>
<body id="kt_body" class="app-blank bgi-size-cover bgi-attachment-fixed bgi-position-center" dir="rtl">

    <!-- خلفية الصفحة -->
    <style>
        body { background-image: url('{{ asset('admin/assets/media/auth/bg10.jpeg') }}'); }
        [data-bs-theme="dark"] body { background-image: url('{{ asset('admin/assets/media/auth/bg10-dark.jpeg') }}'); }
    </style>

    <!-- الجذر الرئيسي -->
    <div class="d-flex flex-column flex-root" id="kt_app_root">
        {{ $slot }}
    </div>

    <!-- ملفات الجافاسكريبت -->
    <script>var hostUrl = "{{ asset('admin/assets/') }}";</script>
    <script src="{{ asset('admin/assets/plugins/global/plugins.bundle.js') }}"></script>
    <script src="{{ asset('admin/assets/js/scripts.bundle.js') }}"></script>
    <script src="{{ asset('admin/assets/js/custom/authentication/sign-in/general.js') }}"></script>
</body>
</html> --}}
