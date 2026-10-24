# إصلاح التشغيل وتحذيرات VS Code — 24 أكتوبر 2026

## السبب المؤكد للـ Fatal error

ظهرت رسالة `Failed opening required .../app/Core/LoginRateLimiter.php` على جهازك. **تحقّقنا أن الملف موجود داخل ZIP الذي أرسلتَه**، ولهذا السبب يُرجَّح أن النسخ أو استخراج الملفات إلى `gym-reservation3` لم يكن كاملًا.

لا تنسخ ملفات المشروع فوق مجلد قديم. احفظ المجلد القديم باسم `gym-reservation3-backup` أولًا، ثم فك الـZIP الجديد في **مجلد جديد وفارغ**. مجلد المشروع داخل الحزمة اسمه `gym-reservation3` ليتوافق مع اسم مشروعك على جهازك.

تحقق من المسار:
`gym-reservation3/app/Core/LoginRateLimiter.php`

من Terminal داخل `gym-reservation3` نفذ:

```powershell
php scripts/check_install.php
```

إذا كان أمر `php` غير معروف، استخدم مسار PHP الخاص بـ XAMPP، مثل:

```powershell
C:\xampp\php\php.exe scripts/check_install.php
```

بعد ظهور `OK`، شغّل MySQL في XAMPP وشغّل PHP Server المعتاد.

## الخطوط الحمراء في VS Code

بعض متغيرات الواجهات مثل `$user` و`$slot` و`$rows` و`$__icon` تُمرَّر باستخدام `renderView()` و`extract()`، ولذلك قد تعتبرها إضافة Intelephense غير معرّفة عند تحليل ملف الـView وحده. أضفنا تلميحات `@var` توثيقية إلى 10 ملفات Views، **بدون تغيير منطق PHP أو HTML الفعلي**.

يُفضَّل فتح `File > Open Folder` واختيار المجلد الجديد `gym-reservation3`؛ انتظر فهرسة Intelephense وأعد تحميل نافذة VS Code باستخدام `Developer: Reload Window` إذا بقيت نتائج قديمة. التلميحات تقلل التحذيرات المرتبطة بالمتغيرات؛ إذا بقي تحذير، راجع رسالته في لوحة Problems بدلاً من تجاهله.

## ما لم نغيّره

- `AuthController.php` وملف حماية الدخول `LoginRateLimiter.php`: مطابقان للنسخة المرفوعة.
- Models وControllers ومسارات الحجوزات وقاعدة البيانات: بلا أي تعديل.
- صور الملاكم ورافع الأثقال، وتصميم CSS و`scene.js`: بدون تعديل.
- لا يوجد أي SQL لحذف البيانات أو إعادة بناء قاعدة البيانات.

## حدود الفحص

يمكن فحص Syntax وواجهة Home والاختبارات المرفقة، لكن بيئة الفحص لا تحتوي `pdo_mysql` أو قاعدة بياناتك المحلية. يجب إجراء اختبار Login ثم إنشاء حصة وحجز حقيقي على XAMPP قبل النشر.
