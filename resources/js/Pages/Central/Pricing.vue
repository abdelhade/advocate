<script setup>
import { Head, Link } from '@inertiajs/vue3';

const allFeatures = [
    { title: 'عدد غير محدود من القضايا والجلسات', desc: 'إدارة شاملة لملفات القضايا وأرقام الدعاوى والدوائر وجدول الجلسات اليومي والترحيل التلقائي.' },
    { title: 'عدد غير محدود من الموكلين', desc: 'حفظ سجلات الموكلين، بيانات التواصل، الأرقام القومية، والتوكيلات والملفات المرفقة.' },
    { title: 'فريق عمل ومحامين غير محدود', desc: 'إضافة جميع المحامين والإداريين بالمكتب مع إدارة الصلاحيات وتوزيع الأعمال.' },
    { title: 'إدارة المهام والأعمال القضائية', desc: 'إنشاء المهام القضائية والإدارية، ربطها بالقضايا، وتعيين المحامي المسؤول ومتابعة الإنجاز.' },
    { title: 'إدارة الفوترة وأتعاب المحاماة', desc: 'إصدار الفواتير والمطالبات المالية، تسجيل أتعاب القضايا والدفعات، وطباعة الفواتير الرسمية.' },
    { title: 'سندات القبض والمصروفات', desc: 'سندات قبض معتمدة، تسجيل المصروفات القضائية ورسوم المحاكم، وتقارير مالية دقيقة لكل قضية.' },
    { title: 'الأرشيف الإلكتروني وإدارة المستندات', desc: 'رفع وتصنيف مستندات القضايا وصور التوكيلات ومذكرات الدفاع بأعلى درجات الأمان.' },
    { title: 'بوابة الموكلين التفاعلية', desc: 'بوابة مخصصة تتيح للموكلين متابعة مواعيد جلساتهم ومستجدات قضاياهم بشفافية واحترافية.' },
    { title: 'نطاق فرعي مخصص للمكتب', desc: 'رابط خاص ومستقل لمكتبك (your-office.jalsateg.com) مع تخصيص هوية المكتب وبياناته.' },
    { title: 'نسخ احتياطي يومي وحماية مشددة', desc: 'تشفير كامل للبيانات، عزل تام لملفات كل مكتب، ونسخ احتياطي يومي آمن ومجاني.' },
];

const faqs = [
    {
        q: 'هل منصة جلسات مجانية بالكامل حقاً؟',
        a: 'نعم! منصة جلسات مجانية 100% بكافة مميزاتها لجميع المحامين ومكاتب المحاماة، وبدون أي رسوم خفية أو فترات تجريبية محدودة.',
    },
    {
        q: 'هل توجد حدود لعدد القضايا أو الموكلين في الخطة المجانية؟',
        a: 'لا توجد أي قيود؛ يمكنك إضافة عدد غير محدود من القضايا، الجلسات، الموكلين، والمستخدمين المعاونين بمكتبك.',
    },
    {
        q: 'هل أحتاج لإدخال بطاقة بنكية أو فيزا للتسجيل؟',
        a: 'إطلاقاً! لا نطلب أي بيانات دفع أو بطاقات بنكية، التسجيل يتم في دقيقة واحدة باستخدام بريدك ورقم هاتفك فقط.',
    },
    {
        q: 'هل بيانات مكتبي وموكلي آمنة ومشفرة؟',
        a: 'بالتأكيد. نعتمد معايير تشفير متقدمة لعزل بيانات كل مكتب بالكامل مع نسخ احتياطي يومي تشفيري وحماية قصوى للسرية المهنية.',
    },
];
</script>

<template>
    <Head title="مجاني بالكامل - منصة جلسات لإدارة مكاتب المحاماة" />

    <div class="min-h-screen bg-stone-50 text-stone-900 font-sans selection:bg-emerald-600 selection:text-white dir-rtl">
        <!-- Top Green Accent Header Line -->
        <div class="fixed top-0 left-0 right-0 h-1.5 bg-emerald-600 z-50"></div>

        <!-- Navbar -->
        <nav class="fixed w-full z-40 bg-white/90 backdrop-blur-md border-b border-stone-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <Link href="/" class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-emerald-700 flex items-center justify-center text-white shadow-lg shadow-emerald-700/20 font-black text-xl">
                            ج
                        </div>
                        <span class="text-2xl font-black text-stone-800 tracking-tight">جلسات</span>
                    </Link>

                    <div class="hidden md:flex items-center gap-8 text-sm font-bold">
                        <Link href="/" class="text-stone-500 hover:text-emerald-700 transition">الرئيسية</Link>
                        <Link href="/#pricing" class="text-emerald-700 font-black">مجاني بالكامل 🎁</Link>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link href="/login" class="text-stone-600 hover:text-stone-900 font-bold text-sm px-4 py-2">تسجيل الدخول</Link>
                        <Link href="/register" class="px-5 py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white font-bold rounded-xl shadow-lg shadow-emerald-600/20 text-sm transition">
                            🚀 سجل مكتبك مجاناً الآن
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Header -->
        <main class="pt-36 pb-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-emerald-50 border border-emerald-200 text-emerald-800 text-xs font-bold mb-6">
                    🎁 منصة جلسات مجانية بالكامل مدى الحياة لكافة المحامين
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-stone-900 tracking-tight leading-tight">
                    جميع ميزات المنصة مجانية <br>
                    <span class="text-emerald-700">بدون اشتراكات وبلا حدود</span>
                </h1>
                <p class="mt-4 text-lg text-stone-600 max-w-2xl mx-auto font-medium">
                    نهدف إلى تمكين المحامين ومكاتب المحاماة في مصر والعالم العربي بأحدث التقنيات السحابية لإدارة القضايا والملفات مجاناً وبكل احترافية.
                </p>
            </div>

            <!-- Free Plan Showcase Card -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
                <div class="relative rounded-3xl bg-white p-8 sm:p-12 border-2 border-emerald-500 shadow-2xl shadow-emerald-950/10 overflow-hidden">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-5 py-1.5 bg-emerald-600 text-white text-xs font-black rounded-full shadow-md">
                        خطة مجانية شاملة مدى الحياة 💎
                    </div>

                    <div class="flex flex-col sm:flex-row items-center justify-between gap-6 pb-8 border-b border-stone-100">
                        <div>
                            <h2 class="text-3xl font-black text-stone-900">الخطة المجانية الكاملة</h2>
                            <p class="text-sm text-stone-500 mt-1 font-semibold">مكتب محاماة متكامل بسعة كاملة وبكافة الموديلات</p>
                        </div>

                        <div class="p-4 rounded-2xl bg-emerald-50 border border-emerald-100 text-center min-w-[200px]">
                            <div class="flex items-baseline justify-center gap-1">
                                <span class="text-5xl font-black text-emerald-700">0</span>
                                <span class="text-base font-bold text-emerald-800">ج.م</span>
                            </div>
                            <span class="text-xs text-emerald-700 font-bold block mt-1">مدى الحياة (بدون أي رسوم)</span>
                        </div>
                    </div>

                    <!-- Features List -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 py-8 border-b border-stone-100">
                        <div v-for="(feat, idx) in allFeatures" :key="idx" class="flex items-start gap-3.5">
                            <div class="w-6 h-6 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center text-xs shrink-0 font-black mt-0.5">
                                ✓
                            </div>
                            <div>
                                <h3 class="text-sm font-black text-stone-900">{{ feat.title }}</h3>
                                <p class="text-xs text-stone-500 mt-0.5 leading-relaxed font-medium">{{ feat.desc }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Bottom Action -->
                    <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                        <div class="text-xs text-stone-500 font-semibold">
                            ✓ تفعيل فوري خلال ثوانٍ • بدون بطاقة بنكية • دعم كامل لكافة الميزات
                        </div>
                        <Link
                            href="/register"
                            class="w-full sm:w-auto px-8 py-4 bg-emerald-600 hover:bg-emerald-700 text-white font-black text-sm rounded-xl transition-all shadow-xl shadow-emerald-700/20 text-center"
                        >
                            سجل مكتبك الآن مجاناً 🚀
                        </Link>
                    </div>
                </div>
            </div>

            <!-- FAQs Section -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-24">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-black text-stone-900">الأسئلة الشائعة</h2>
                    <p class="text-stone-500 text-sm mt-2 font-semibold">كل ما تحتاج لمعرفته حول استخدام منصة جلسات المجانية</p>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(faq, fIndex) in faqs"
                        :key="fIndex"
                        class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm"
                    >
                        <h3 class="text-base font-black text-stone-900 mb-2 flex items-center gap-2">
                            <span class="text-emerald-600">❓</span>
                            <span>{{ faq.q }}</span>
                        </h3>
                        <p class="text-sm text-stone-600 leading-relaxed font-medium pr-6">
                            {{ faq.a }}
                        </p>
                    </div>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-stone-200 bg-white py-12 text-center text-stone-500 text-sm">
            <div class="max-w-7xl mx-auto px-4 flex flex-col sm:flex-row justify-between items-center gap-4">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-emerald-700 flex items-center justify-center text-white font-bold">
                        ج
                    </div>
                    <span class="text-lg font-bold text-stone-900">منصة جلسات</span>
                </div>
                <p class="text-xs text-stone-500">
                    &copy; {{ new Date().getFullYear() }} منصة جلسات لإدارة مكاتب المحاماة. جميع الحقوق محفوظة.
                </p>
            </div>
        </footer>
    </div>
</template>
