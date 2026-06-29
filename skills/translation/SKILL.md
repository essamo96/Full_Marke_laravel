---
name: translation
description: إضافة أو تعديل نصوص الترجمة (lang/ar, lang/en) ودعم الحقول متعددة اللغات في الموديلات والفورمات بمشروع Yabous.
---

# Skill: إدارة الترجمة في Yabous

المرجع الكامل: `docs/PATTERN_DESIGN.md` (قسم 6، 10، 11).

## نصوص الواجهة الثابتة (UI strings)
- المكان: `lang/ar/app.php` و `lang/en/app.php` (وكذلك `validation.php`, `errors.php`, `permissions.php` بحسب النوع).
- **كل مفتاح جديد يُضاف بالعربي والإنجليزي بنفس الوقت** — لا تترك لغة بدون مقابل.
- الاستخدام:
  - Blade: `\App\Helpers\translate('key')` أو `__('app.key')`.
  - Controller/Model: `__('app.key')`.
  - رسائل Form Request: `__('app.validation_{field}_{rule}')`.

## حقول الموديل المتعددة اللغات (Translation Models)
- لا حقول ثابتة `name_ar`/`name_en` بالجدول الأساسي. بل جدول `{module}_translations` منفصل بحقل `locale`.
- الفورم: حقل واحد لكل تبويب لغة بالاسم `name="{locale}[name]"`، يُبنى بـ loop على `Language::where('status', 1)->get()` — **لا تكتب تبويب `ar`/`en` يدويًا بالكود**، حتى تضاف لغة جديدة تلقائيًا بدون تعديل الفورم.
- الموديل: علاقة `translation()` (مفردة، مقيدة بـ `app()->getLocale()`) للعرض العام، و`translations()` (جمع) لكل اللغات بصفحة التعديل.
- الكونترولر: حفظ/تحديث الترجمات بـ loop واحد عام (`collect($request->except([...basic, '_token']))->each(...)`) بدل كتابة كود مكرر لكل لغة.

## أخطاء شائعة
- إضافة مفتاح بملف `ar/app.php` فقط بدون `en/app.php` (يظهر المفتاح الخام بالواجهة الإنجليزية).
- تثبيت قائمة لغات بالكود (`['ar','en']`) بدل القراءة من جدول `languages`.
- خلط نصوص الترجمة الثابتة (UI) مع حقول الموديل المترجمة (Translation Model) — هما آليتان مختلفتان تمامًا.
