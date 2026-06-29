# دليل الباترن ديزاين - مشروع Yabous

هذا الملف يوثّق الـ Pattern المستخدم فعليًا في الكود الحالي (Laravel) لكل من **لوحة التحكم (Admin)** و **الواجهة الأمامية (Frontend)**، حتى يبقى أي تطوير جديد متسقًا مع الموجود، نظيفًا (Clean Code)، ومرنًا قابلًا للتعديل بسهولة.

> القاعدة الذهبية: **لا تخترع نمطًا جديدًا** لميزة جديدة. ابحث عن أقرب موديول مشابه (مثل `categories`) واستنسخ بنيته حرفيًا.

---

## 1. بنية تقسيم الملفات (Folder Structure)

```
app/
  Http/
    Controllers/
      Admin/          ← كل كونترولرات لوحة التحكم (تمتد من AdminController)
      Frontend/        ← كونترولرات واجهة أمامية مخصّصة (News, Certificates...)
      Auth/             ← كونترولرات المصادقة الافتراضية لـ Laravel Breeze
      *.php             ← كونترولرات واجهة أمامية عامة (HomepageController, BookingController...)
    Requests/
      Admin/            ← Form Request لكل موديول إدارة (CategoryRequest, BlogRequest...)
  Models/
    {Model}.php          ← الموديل الأساسي
    {Model}Translation.php ← موديل الترجمة المرتبط (لكل موديول متعدد اللغات)
routes/
  web.php               ← نقطة الدخول، يحمّل بقية ملفات الراوت بـ require
  {module}.php          ← ملف راوت مستقل لكل موديول (categories.php, blogs.php...)
resources/
  views/
    admin/
      layout/            ← القوالب الأساسية والـ sidebar والـ partials المشتركة
      {module}/
        view.blade.php    ← صفحة index (DataTable)
        add.blade.php     ← صفحة create + edit (نفس الفورم لكليهما)
        parts/            ← أعمدة DataTable الجزئية (actions, status, general, modal...)
    frontend/
      {module}/           ← صفحات الواجهة الأمامية حسب الموديول
      layouts/            ← قوالب الواجهة الأمامية
    layouts/                ← قوالب Breeze الافتراضية (auth/profile)
    components/             ← Blade components مشتركة (+ frontend/ للمكونات الخاصة بالواجهة)
database/
  migrations/             ← migration منفصل لكل جدول + جدول ترجمة منفصل
lang/
  ar/, en/                ← ملفات الترجمة (app.php, validation.php, errors.php, permissions.php, datatables.json)
```

**القاعدة:** كل "موديول" (Category, Blog, News...) له 4 أجزاء متطابقة الاسم:
1. Controller في `app/Http/Controllers/Admin/{Module}sController.php`
2. Route file في `routes/{module}.php`
3. Views في `resources/views/admin/{module}/`
4. Model (+ Translation Model إذا كان متعدد اللغات)

---

## 2. الكونترولر الأب (AdminController)

كل كونترولرات لوحة التحكم **يجب أن تمتد من** `App\Http\Controllers\Admin\AdminController`.

```php
class AdminController extends BaseController
{
    public static $data = [];   // مصفوفة مشتركة بين كل الكونترولرات الفرعية

    public function __construct()
    {
        // يبني $data['sidebar'] من PermissionsGroup
        // يحسب عدادات الإشعارات/الحجوزات/التواصل لإظهارها بالـ sidebar
        // يحدد $data['current_route'] الحالي بناءً على اسم الراوت
    }
}
```

الكونترولر الفرعي:
```php
class CategoriesController extends AdminController
{
    protected $path; // اسم الموديول، يُستخدم في كل أسماء الـ view والـ route

    public function __construct()
    {
        parent::__construct();
        parent::$data['active_menu'] = 'categories';
        $this->path = 'categories';
    }
}
```

**لا تكرر منطق الصلاحيات/الإشعارات/السايدبار في كونترولر فرعي** — هو موروث تلقائيًا من الأب.

---

## 3. نمط CRUD في الكونترولر

تسمية الميثودز ثابتة بصيغة `getXxx` / `postXxx` (وليس resource controller الافتراضي):

| الميثود | الغرض |
|---|---|
| `getIndex()` | يعرض صفحة `view.blade.php` (الجدول) |
| `getList(Request $request)` | يُغذّي DataTables عبر Ajax (`Yajra\DataTables`) |
| `getAdd()` | يعرض فورم الإضافة (`add.blade.php`, `info = null`) |
| `postAdd(XxxRequest $request)` | يخزّن السجل + الترجمات |
| `getEdit(Request $request, $id)` | يعرض نفس فورم الإضافة، لكن `info` معبّى ويُفكّ تشفير `$id` |
| `postEdit(XxxRequest $request, $id)` | يحدّث السجل + الترجمات (`updateOrCreate`) |
| `postStatus(Request $request)` | تبديل حالة تفعيل/تعطيل (Ajax JSON) |
| `postDelete(Request $request)` | حذف (Ajax JSON) |

### نقاط أساسية يجب اتباعها دومًا:

1. **تشفير الـ ID في الروابط**: `Crypt::encrypt($id)` عند العرض، و`Crypt::decrypt($id)` داخل `try/catch` عند الاستقبال. إن فشل فك التشفير → flash رسالة خطأ + redirect لصفحة الـ view.
2. **الترجمات تُعالج بـ loop على اللغات**، لا حقول ثابتة:
```php
collect($request->except([...basic fields..., '_token']))
    ->each(function ($translations, $locale) use ($record) {
        XxxTranslation::updateOrCreate(
            ['xxx_id' => $record->id, 'locale' => $locale],
            ['name' => $translations['name'] ?? null]
        );
    });
```
3. **التحقق (Validation) دائمًا عبر Form Request مخصص** في `App\Http\Requests\Admin\{Module}Request`، أبدًا لا تتحقق يدويًا داخل الكونترولر.
4. **رسائل النجاح/الفشل عبر `__('app.xxx')`** و `session()->flash('success'|'danger', ...)`.
5. **مسح كاش الصلاحيات بعد كل تعديل مؤثر**: `Cache::forget('spatie.permission.cache');`
6. **أعمدة DataTable الخاصة (status, actions, روابط معاد استخدامها) تُرجع كـ Blade view جزئي** (`admin.{module}.parts.xxx`) لا HTML inline داخل الكونترولر.
7. **الأعمال المنطقية المعقّدة (بحث/فلترة) توضع كميثود في الموديل نفسه** (مثل `Category::getSearch()`)، لا في الكونترولر مباشرة.

---

## 4. ملفات الراوت (Routes)

- لا يوجد `Route::resource`. كل راوت يُكتب يدويًا بصيغة array مع `as`, `middleware`, `uses`:
```php
Route::get('categories', [
    'as' => 'categories.view',
    'middleware' => ['permission:admin.categories.view'],
    'uses' => 'CategoriesController@getIndex'
]);
```
- اسم الـ route name دائمًا `{module}.{action}` (`categories.view`, `categories.add`, `categories.edit`, `categories.delete`, `categories.status`).
- middleware الصلاحية بصيغة ثابتة: `permission:admin.{module}.{action}` (يتطابق مع صلاحيات Spatie المسجّلة في `PermissionsGroup`).
- كل موديول له **ملف راوت منفصل** في `routes/{module}.php`، ويُستدعى من `web.php` داخل الـ group الخاص بالأدمن عبر:
```php
require __DIR__ . '/{module}.php';
```
- `routes/web.php` يحتوي فقط: راوتات frontend العامة + إعداد الـ group الخاص بالأدمن (`namespace`, `prefix admin`, `middleware [auth:admin, setlocale]`) + استدعاء كل ملفات الموديولات بـ `require`.

**عند إضافة موديول جديد:**
1. أنشئ `routes/{module}.php` بنفس صيغة `categories.php`.
2. أضف `require __DIR__ . '/{module}.php';` داخل group الأدمن في `web.php`.
3. أضف صلاحيات الموديول في `PermissionsGroup`/seeder الصلاحيات.

---

## 5. القائمة الجانبية (Sidebar)

- لا تُكتب يدويًا في Blade كقائمة ثابتة. تُبنى **ديناميكيًا من قاعدة البيانات** عبر `PermissionsGroup::getAllParentPermissionGroup()` ويُمرَّر كـ `$data['sidebar']` من `AdminController`.
- ملف العرض: `resources/views/admin/layout/sidebar_menu.blade.php`.
- البنية: عناصر أب (`mychild` فاضية أو فيها أبناء) → عرض شرطي بـ `@can('admin.{module}.view')`.
- التحكم بالعنصر النشط: مقارنة `active_menu` (مضبوط في كونترولر كل موديول) مع `$menu_item->name`.
- اسم/أيقونة كل عنصر مُترجمة من حقول `name_ar`/`name_en` و `icon`/`color` في جدول `permissions_groups`.

**لإضافة موديول جديد إلى القائمة:** لا تعدّل ملف Blade — أضف سطر/سجل جديد في جدول `permissions_groups` (عبر seeder أو شاشة `permissions_group`) بنفس `name` المستخدم في الراوت.

---

## 6. ملفات الفيوز (Views) لكل موديول

```
resources/views/admin/{module}/
  view.blade.php        ← index: جدول + بحث + زر إضافة + تضمين parts/modal
  add.blade.php          ← فورم موحّد للإضافة والتعديل (يفرّق بـ isset($info))
  parts/
    actions.blade.php     ← أزرار تعديل/حذف لكل صف
    status.blade.php       ← toggle حالة التفعيل
    general.blade.php       ← خلية عرض اسم مرتبط (شركة/قسم أب...)
    modal.blade.php          ← مودال التأكيد على الحذف (يُضمَّن في view.blade.php)
```

قواعد ثابتة:
- `view.blade.php` يمتد `@extends('admin.layout.main_master')`.
- عمود الجدول الديناميكي يُعرَّف بـ JS array `columns` يطابق أسماء `addColumn/editColumn` في الكونترولر، ثم `@include('admin.layout.masterLayouts.datatableMaster')` لتفعيل DataTables Ajax.
- النصوص الثابتة دائمًا عبر `\App\Helpers\translate('key')` أو `__('app.key')`، **لا نص عربي/إنجليزي مكتوب مباشرة بالـ Blade**.
- `add.blade.php` يبني حقول الترجمة بـ loop على `$languages` (`Language::where('status',1)->get()`) — Tab لكل لغة بنفس أسماء الحقول `name="{locale}[name]"` لتطابق منطق `collect($request->except(...))` بالكونترولر.
- الحذف عبر مودال موحّد (`parts/modal.blade.php`) + Ajax POST لراوت `{module}.delete`، والتفعيل/التعطيل عبر Ajax POST لراوت `{module}.status`.

---

## 7. الواجهة الأمامية (Frontend)

- كونترولرات عامة في `app/Http/Controllers/*.php` (HomepageController, BookingController...) أو `Frontend/` للموديولات المعزولة (News, Certificates).
- كل الراوتات العامة مجمّعة داخل `LaravelLocalization::setLocale()` group في `web.php` (دعم تعدد اللغات بالـ prefix التلقائي `/ar`, `/en`).
- Views تحت `resources/views/frontend/{module}/` و layout مشترك في `resources/views/frontend/layouts/`.
- لا DataTables بالواجهة الأمامية — استعلامات مباشرة من الكونترولر إلى الـ view (Pagination العادي لـ Laravel عند الحاجة).
- استخدم نفس موديلات الأدمن (Model + Translation) — لا تُكرّر موديلات مستقلة للواجهة الأمامية.

---

## 8. الموديلات والعلاقات

```php
class Category extends Model
{
    protected $fillable = [...];   // فقط الحقول الأساسية، لا حقول الترجمة

    public function translations() { return $this->hasMany(CategoryTranslation::class); }
    public function translation()  { return $this->hasOne(CategoryTranslation::class)->where('locale', app()->getLocale()); }

    public function company() { return $this->belongsTo(Company::class); }
    public function parent()  { return $this->belongsTo(Category::class, 'category_id'); }
    public function children(){ return $this->hasMany(Category::class, 'category_id'); }

    public function scopeActive($query) { return $query->where('status', 1); }

    // منطق بحث/فلترة معقّد يوضع هنا كميثود عامة، لا في الكونترولر
    public function getSearch($name, $companies = null, $emp_id = 0) { ... }
}
```

قواعد ثابتة:
- كل موديول متعدد اللغات له **موديل ترجمة منفصل** (`{Module}Translation`) بعلاقة `hasMany`/`hasOne`.
- علاقة `translation()` (singular) دائمًا تُقيَّد بـ `app()->getLocale()` لعرض اللغة الحالية فقط، و`translations()` (plural) لكل اللغات (تُستخدم بصفحة التعديل).
- العلاقات الهرمية (أب/ابن) بنفس الموديل تُسمّى `parent()`/`children()` على حقل FK خاص (`category_id` على نفس الجدول `categories`).

---

## 9. Migrations

- جدول أساسي + جدول ترجمة منفصل بنفس الاسم + `_translations`:
```php
Schema::create('advertisement_translations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('advertisement_id');
    $table->string('locale')->index();
    $table->string('title')->nullable();
    $table->text('description')->nullable();
    $table->timestamps();

    $table->foreign('advertisement_id')->references('id')->on('advertisements')->onDelete('cascade');
});
```
- اسم migration: `{date}_create_{table}_table.php` أو `{date}_add_{column}_to_{table}_table.php` للتعديلات اللاحقة — **لا تعدّل migration قديم منفّذ بالإنتاج**، أنشئ migration جديد دائمًا.
- الحقول المشتركة في الجدول الأساسي: `status` (tinyint 0/1)، `sort` (ترتيب العرض)، حقول FK بدون نوع enum صريح إلا عند الحاجة.
- الترجمة دائمًا: `locale` (string, indexed) + FK على الجدول الأب بـ `onDelete('cascade')`.

---

## 10. ملفات الترجمة (lang/)

```
lang/
  ar/  app.php, validation.php, errors.php, permissions.php, datatables.json
  en/  (نفس الأسماء)
```

- `app.php`: كل نصوص الواجهة + رسائل الفلاش (`insert_success`, `update_success`, `delete_success`, `not_found`, `execution_error`...) ومفاتيح كل حقل (`category_name`, `selectCompany`...).
- `validation.php`: رسائل التحقق المخصّصة المستخدمة داخل `messages()` بكل Form Request.
- `permissions.php`: تسميات الصلاحيات المعروضة بشاشة الأدوار.
- `datatables.json`: ترجمة واجهة مكتبة DataTables (يُجلب أيضًا عبر route `lang/{locale}/datatables.json`).
- **أضف المفتاح في كل لغة فورًا عند إضافته** — لا تترك مفتاحًا في `ar` بدون مقابل بـ `en` (أو العكس).
- الوصول من الكونترولر/الموديل: `__('app.key')`. من الـ Blade: `\App\Helpers\translate('key')` (helper مخصص بالمشروع) أو `__('app.key')` مباشرة.

---

## 11. Form Requests

```php
class CategoryRequest extends FormRequest
{
    public function authorize(): bool { return true; } // الصلاحية تُفحص بالـ route middleware لا هنا

    public function rules(): array
    {
        $baseRules = [ ... ]; // حقول الجدول الأساسي
        foreach (Language::where('status', 1)->pluck('prefix') as $lang) {
            $langRules["{$lang}.name"] = 'required|string|min:3|max:191';
        }
        return array_merge($baseRules, $langRules);
    }

    public function messages(): array { return [ 'field.rule' => __('app.validation_field_rule'), ... ]; }
}
```
- نفس الـ Request يُستخدم لـ `postAdd` و`postEdit` (لا تنشئ Request منفصل للتحديث إلا لاختلاف جوهري في القواعد).
- قواعد حقول الترجمة تُبنى ديناميكيًا (loop على اللغات الفعّالة)، لا حقول `ar.name` / `en.name` مكتوبة يدويًا.

---

## 12. خلاصة Checklist لإضافة موديول CRUD جديد

1. [ ] Migration: جدول أساسي + جدول `_translations` (إن وُجدت حقول مترجمة).
2. [ ] Model: `{Module}` + `{Module}Translation` بعلاقات `translation()`/`translations()`.
3. [ ] Form Request: `App\Http\Requests\Admin\{Module}Request`.
4. [ ] Controller: `App\Http\Controllers\Admin\{Module}sController extends AdminController` بنفس ميثودز getIndex/getList/getAdd/postAdd/getEdit/postEdit/postStatus/postDelete.
5. [ ] Route file: `routes/{module}.php` + `require` بـ `web.php`.
6. [ ] Views: `resources/views/admin/{module}/` (view, add, parts/actions|status|general|modal).
7. [ ] Sidebar: سجل جديد في `permissions_groups` + صلاحيات Spatie (`admin.{module}.view/add/edit/delete/status`).
8. [ ] Translations: مفاتيح جديدة في `lang/ar/app.php` و `lang/en/app.php` (+ `validation.php` عند الحاجة).
9. [ ] (اختياري) Frontend: إذا الموديول يحتاج عرضًا عامًا، أنشئ كونترولر/views تحت `frontend/`.

---

## ملاحظة حول المرونة

هذا النمط مصمم ليكون **قابلًا للتمديد بدون كسر القديم**:
- إضافة عمود جديد لموديول قائم → migration جديد فقط (`add_x_to_y_table`)، بدون تعديل أي migration سابق.
- إضافة لغة جديدة → تُدار تلقائيًا لأن حقول الترجمة والفورمات تُبنى من جدول `languages`، لا حقول ثابتة بالكود.
- إضافة صلاحية فرعية جديدة → سجل جديد بـ `permissions_groups`/Spatie فقط، بدون لمس الـ sidebar Blade.
