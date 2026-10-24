# Simple MVC/OOP map (Arabic)

- `index.php`: باب الدخول الوحيد للمتصفح. يستقبل `page` ويقرر أي Controller يعمل.
- `app/Core/Database.php`: `Database` يجهز PDO اتصال MySQL.
- `app/Models/User.php`: `User` يتعامل مع جدول users.
- `app/Models/GymClass.php`: `GymClass` يقرأ ويغير جدول time_slots.
- `app/Models/Reservation.php`: `Reservation` ينفذ الحجز، الإلغاء، والحالات.
- `app/Controllers/AuthController.php`: يعالج Register/Login/Logout.
- `app/Controllers/ClassController.php`: يعالج عرض/إضافة/تعديل/تعطيل/حذف المواعيد.
- `app/Controllers/ReservationController.php`: طلبات الحجز والإلغاء للمستخدم.
- `app/Controllers/AdminController.php`: إحصائيات المدير، المستخدمين، مراجعة الحجوزات.
- `app/Middleware/AuthMiddleware.php`: يتحقق من هوية وصلاحية المستخدم في كل طلب.
- `resources/views/`: ملفات HTML/PHP للعرض؛ لا تضع استعلامات SQL بداخلها.
- `app/Core/helpers.php`: دوال صغيرة مشتركة للحماية، تحويل الصفحات، وعرض النماذج.

## Example: booking journey

1. `classes/index.php` يعرض حصة عبر جدول `time_slots`.
2. الضغط Book يبعت POST لـ `index.php?page=book-class`.
3. index يفحص الجلسة ويشغّل `ReservationController::store()`.
4. Controller يتحقق من CSRF وslot id وnote.
5. `Reservation::book()` يقفل سطر الحصة بـ `FOR UPDATE` ويفحص التكرار والسعة.
6. إذا كل شيء صحيح، يضيف Pending داخل Transaction؛ بعدها Redirect.

## Reservation states
`Pending` → `Confirmed`, `Rejected` أو `Cancelled`. `Confirmed` → `Cancelled`.
`Rejected` و `Cancelled` نهائيتان. حالات Cancelled/Rejected لا تستهلك المقاعد. المستخدم لا يحجز نفس الموعد مجددًا بعد الإلغاء حسب قاعدة عدم التكرار.

## Soft delete
عند الضغط Delete على موعد بلا Pending/Confirmed، يصبح `is_deleted=1` للاحتفاظ بسجلات الحجوزات القديمة وربط Foreign Key؛ يختفي من قوائم الحصص.

- `app/Core/LoginRateLimiter.php`: قيد محاولات الدخول بين الجلسات باستعمال ملفات محلية؛ لا يغير جدول users.
