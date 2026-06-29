---
name: admin-crud-module
description: إنشاء موديول CRUD كامل جديد في لوحة تحكم Yabous (Migration + Model + Translation + Request + Controller + Routes + Views + Sidebar + Lang) باتباع الباترن الموجود فعليًا في المشروع (مثل categories/blogs/news).
---

# Skill: بناء موديول CRUD في لوحة التحكم (Admin)

استخدم هذا الـ skill عند طلب المستخدم "أضف صفحة/شاشة جديدة في لوحة التحكم" أو "أنشئ CRUD لـ X".

المرجع الكامل للباترن: `docs/PATTERN_DESIGN.md` في جذر المشروع. اقرأه أولًا إذا لم يكن محمّلًا في الذاكرة.

## أقرب مثال جاهز للنسخ
الموديول `categories` هو المرجع الأنظف والأبسط:
- `app/Http/Controllers/Admin/CategoriesController.php`
- `routes/categories.php`
- `app/Http/Requests/Admin/CategoryRequest.php`
- `app/Models/Category.php` + `app/Models/CategoryTranslation.php`
- `resources/views/admin/categories/` (view.blade.php, add.blade.php, parts/)
- `database/migrations/*_create_categories_table.php` + `*_create_category_translations_table.php`

**انسخ هذه الملفات حرفيًا وغيّر الاسم فقط**، لا تعيد كتابة المنطق من الصفر.

## خطوات التنفيذ بالترتيب

1. **اسأل/حدد** اسم الموديول (singular/plural)، الحقول الأساسية، وهل يحتاج ترجمة (name متعدد اللغات) أم لا.
2. **Migration**: أنشئ `{date}_create_{modules}_table.php`. إذا متعدد اللغات أضف `{date}_create_{module}_translations_table.php` بنفس بنية `advertisement_translations` (locale + FK cascade).
3. **Model**: أنشئ `{Module}.php` بـ `$fillable` للحقول الأساسية فقط، وعلاقات `translation()`/`translations()` إن وجدت، وأي علاقات FK مطلوبة (`belongsTo`/`hasMany`).
4. **Translation Model** (إن وجد): `{Module}Translation.php` بـ `$fillable` يضم `{module}_id, locale, name...`.
5. **Form Request**: `app/Http/Requests/Admin/{Module}Request.php` — قواعد الحقول الأساسية + loop على `Language::where('status',1)` لحقول الترجمة، و`messages()` بمفاتيح `__('app.validation_*')`.
6. **Controller**: `app/Http/Controllers/Admin/{Module}sController.php extends AdminController` بنفس ميثودز categories بالضبط (getIndex, getList مع DataTables، getAdd, postAdd, getEdit, postEdit, postStatus, postDelete). لا تنسَ `Cache::forget('spatie.permission.cache')` بعد أي تعديل، وتشفير/فك تشفير الـ id بـ `Crypt`.
7. **Routes**: `routes/{module}.php` بنفس صيغة array (`as`, `middleware: permission:admin.{module}.{action}`, `uses`). أضف `require __DIR__ . '/{module}.php';` داخل group الأدمن في `routes/web.php` (قرب الموديولات المشابهة).
8. **Views**: مجلد `resources/views/admin/{module}/` بنفس بنية categories (view.blade.php + add.blade.php + parts/actions|status|general|modal.blade.php). حدّث أسماء الأعمدة بالـ JS columns ليطابق addColumn بالكونترولر.
9. **الصلاحيات والسايدبار**: لا تعدّل ملف Blade الخاص بالـ sidebar. أضف سجلًا في جدول `permissions_groups` (أو seeder الصلاحيات الموجود) بنفس `name` المستخدم بالراوت، وأنشئ صلاحيات Spatie: `admin.{module}.view/add/edit/delete/status`.
10. **الترجمة**: أضف كل المفاتيح النصية الجديدة في `lang/ar/app.php` **و** `lang/en/app.php` بآن واحد (وفي `validation.php` إن استخدمت رسائل مخصصة).
11. **تحقق نهائي**: شغّل `php artisan route:list --name={module}` للتأكد من تسجيل كل الراوتات، وافتح الصفحة فعليًا للتأكد من DataTable يعمل والفورم يحفظ.

## أخطاء شائعة يجب تجنبها
- نسيان `Cache::forget('spatie.permission.cache')` بعد إضافة/تعديل/حذف.
- كتابة نص عربي/إنجليزي مباشر بالـ Blade بدل `\App\Helpers\translate()` أو `__('app.key')`.
- استخدام `Route::resource` بدل صيغة array اليدوية المتسقة مع باقي المشروع.
- تعديل migration قديم منفّذ بالإنتاج بدل إنشاء migration جديد.
- وضع منطق بحث/فلترة معقّد بالكونترولر بدل وضعه كميثود بالموديل (مثل `getSearch`).
