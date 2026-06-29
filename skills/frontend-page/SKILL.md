---
name: frontend-page
description: إنشاء صفحة جديدة بالواجهة الأمامية (Frontend) لمشروع Yabous بنفس نمط صفحات news/certificates/blogs الحالية، مع دعم تعدد اللغات وإعادة استخدام الموديلات الموجودة بدون تكرارها.
---

# Skill: بناء صفحة في الواجهة الأمامية (Frontend)

استخدم هذا الـ skill عند طلب "أضف صفحة عامة للزوار" أو "صفحة جديدة بالموقع" (غير لوحة التحكم).

المرجع الكامل: `docs/PATTERN_DESIGN.md` (قسم 7 - الواجهة الأمامية).

## أقرب مثال جاهز للنسخ
`app/Http/Controllers/Frontend/NewsController.php` + `resources/views/frontend/news/`.

## خطوات التنفيذ

1. **لا تُنشئ موديل جديد** إن كانت البيانات موجودة بالفعل بموديل الأدمن (مثل `News`, `Blog`, `Certificate`) — استخدم نفس الموديل وعلاقة `translation()` للغة الحالية فقط.
2. **Controller**: ضعه في `app/Http/Controllers/Frontend/{Module}Controller.php` إذا الموديول معزول، أو ميثود جديدة بكونترولر عام مناسب (`HomepageController`) إذا كانت صفحة بسيطة مرتبطة بالموقع الرئيسي.
3. **Routes**: أضف الراوت داخل group الـ `LaravelLocalization::setLocale()` بملف `routes/web.php` (وليس داخل group الأدمن) لضمان دعم prefix اللغة (`/ar`, `/en`) تلقائيًا.
4. **Views**: أنشئ `resources/views/frontend/{module}/` مع `@extends('frontend.layouts....')` المناسب. استخدم نفس مكونات `resources/views/components/frontend/` الموجودة بدل تكرار HTML.
5. **الترجمة**: استخدم `$model->translation->field` (مرتبط بـ `app()->getLocale()` تلقائيًا) — لا تكتب شرط `if(locale == 'ar')` يدويًا.
6. **بدون DataTables**: استخدم استعلام مباشر + `paginate()` العادي لـ Laravel إن وجدت قوائم طويلة.

## أخطاء شائعة
- إنشاء موديل/جدول مكرر للواجهة الأمامية بدل إعادة استخدام موديل الأدمن.
- وضع راوت الواجهة الأمامية داخل group الأدمن (`prefix admin`) بالخطأ.
- نسيان أن الراوت يجب أن يكون داخل group اللغة لدعم تعدد اللغات.
