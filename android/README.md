# تطبيق جلسات للأندرويد (Jalsat Android App)

تطبيق أندرويد متكامل مربوط بموقع **[jalsateg.com](https://jalsateg.com)** بصيغة **Android App Bundle (.aab)** الجاهزة للنشر المباشر على **Google Play Console**، بالإضافة إلى ملف **APK** للتجربة المباشرة على الهاتف.

---

## 📦 ملفات الإصدار الجاهزة

تم حفظ النسخ النهائية الجاهزة في المجلد:
`D:\laravel\advocate\dist\`

1. **ملف حزمة التطبيق لمتجر جوجل بلاي (AAB):**
   - المسار: `D:\laravel\advocate\dist\jalsat-release.aab`
   - أو المسار الداخلي: `android/app/build/outputs/bundle/release/app-release.aab`
2. **ملف التثبيت المباشر على الهاتف (APK):**
   - المسار: `D:\laravel\advocate\dist\jalsat-release.apk`
   - أو المسار الداخلي: `android/app/build/outputs/apk/release/app-release.apk`

---

## ⚙️ مواصفات التطبيق
- **اسم التطبيق:** جلسات
- **معرف الحزمة (Package ID):** `com.jalsateg.app`
- **الإصدار (Version):** `1.0.0` (Code: 1)
- **الهدف (Target SDK):** Android 14 (API 34)
- **أدنى إصدار مدعوم (Min SDK):** Android 7.0 (API 24)

---

## 🔑 بيانات مفتاح التوقيع (Keystore)
تم إنشاء مفتاح توقيع رقمي صالح لمدة 25 سنة ومحفوظ في:
`android/app/release.keystore`

- **Key Store Password:** `jalsateg2026`
- **Key Alias:** `jalsateg`
- **Key Password:** `jalsateg2026`

> ⚠️ **ملاحظة هامة:** احتفظ بنسخة احتياطية من ملف `release.keystore` وبياناته، لأن متجر Google Play يتطلب نفس المفتاح لتحديثات التطبيق المستقبلية.

---

## 🚀 المميزات المدمجة في التطبيق
1. **دعم النطاق الرئيسي والنطاقات الفرعية:** يعمل مع `https://jalsateg.com` وكافة نطاقات مكاتب المحاماة التابعة للمنصة `https://*.jalsateg.com`.
2. **شاشة افتتاحية رسمية (Splash Screen):** خلفية داكنة مع شعار منصة جلسات الرسمي.
3. **رفع الملفات والمستندات (File Chooser):** إمكانية إرفاق ملفات القضايا والتوكيلات والتقاط الصور بالكاميرا مباشرة.
4. **تنزيل المستندات والفواتير (Download Manager):** تنزيل ملفات الـ PDF ومستندات القضايا إلى مجلد التحميلات بالهاتف مع إشعارات النظام.
5. **شاشة عند انقطاع الإنترنت (Offline Screen):** تصميم أنيق مع زر "إعادة المحاولة" عند تعذر الاتصال.
6. **التحكم بزر الرجوع (Back Button):** التنقل للخلف داخل صفحات الموقع بدلاً من إغلاق التطبيق.
7. **الروابط الخارجية:** روابط الواتساب والاتصال الهاتفي والبريد الإلكتروني تفتح في التطبيقات الخارجية تلقائياً.

---

## 🛠️ أوامر إعادة البناء والتحديث

في حال أردت إعادة إنشاء الملفات مستقبلاً:

### بناء ملف AAB لمتجر جوجل بلاي:
```powershell
cd d:\laravel\advocate\android
.\gradlew.bat bundleRelease
```

### بناء ملف APK للتثبيت المباشر:
```powershell
cd d:\laravel\advocate\android
.\gradlew.bat assembleRelease
```

