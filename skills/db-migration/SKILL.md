---
name: db-migration
description: إنشاء أو تعديل Migrations في مشروع Yabous باتباع نمط الجداول + جداول الترجمة المنفصلة، بدون كسر migrations سابقة منفّذة على بيئة الإنتاج.
---

# Skill: بناء Migration بنمط Yabous

المرجع الكامل: `docs/PATTERN_DESIGN.md` (قسم 9 - Migrations).

## القاعدة الأهم
**لا تُعدّل أبدًا migration قديم منفّذ سابقًا.** أي تغيير على جدول موجود = migration جديد بصيغة:
- `{date}_add_{column}_to_{table}_table.php` لإضافة حقل.
- `{date}_update_{x}_to_{table}.php` لتعديل بيانات/نوع حقل موجود.

## جدول أساسي + جدول ترجمة (إذا الموديول متعدد اللغات)

مرجع حرفي: `database/migrations/2026_05_08_163130_create_advertisement_translations_table.php`.

```php
Schema::create('{module}s', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('company_id')->nullable();
    $table->string('slug')->nullable();
    $table->integer('sort')->default(0);
    $table->tinyInteger('status')->default(1); // 0/1
    $table->timestamps();
});

Schema::create('{module}_translations', function (Blueprint $table) {
    $table->id();
    $table->unsignedBigInteger('{module}_id');
    $table->string('locale')->index();
    $table->string('name')->nullable();
    $table->timestamps();

    $table->foreign('{module}_id')->references('id')->on('{module}s')->onDelete('cascade');
});
```

## خطوات
1. حدد إن كان الموديول يحتاج جدول ترجمة (أي حقل نصي يظهر بأكثر من لغة) أم لا.
2. الحقول المشتركة المتوقعة بالجدول الأساسي: `status` (tinyint)، `sort` (ترتيب)، FKs بدون enum صريح إلا لزوم واضح.
3. جدول الترجمة دائمًا: `locale` (indexed) + FK بـ `onDelete('cascade')` — بدون حقول مشتركة أخرى غير النصوص المترجمة.
4. شغّل `php artisan migrate` محليًا للتأكد من عدم وجود تعارض اسم جدول/فهرس.
5. لا تستخدم `Schema::table` على جدول قديم لإعادة كتابة بنية كاملة — فقط لإضافة/حذف حقل واحد أو فهرس.
