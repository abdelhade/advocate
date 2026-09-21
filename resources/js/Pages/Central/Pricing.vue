<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { ref } from 'vue';

const isAnnual = ref(true);

const plans = [
    {
        name: 'المجانية',
        tagline: 'للمحامين المبتدئين أو التجربة الأولوية',
        monthlyPrice: 0,
        annualPrice: 0,
        badge: null,
        highlight: false,
        features: [
            { text: '1 مستخدم (محامي واحد)', included: true },
            { text: '10 موكلين كحد أقصى', included: true },
            { text: '100 قضية كحد أقصى', included: true },
            { text: 'إدارة المهام', included: false },
            { text: 'إدارة الفوترة والأتعاب', included: false },
            { text: 'إدارة المستندات والتوثيق', included: false },
            { text: 'بوابة الموكلين', included: false },
        ],
        buttonText: 'ابدأ مجاناً الآن',
        buttonClass: 'bg-stone-100 hover:bg-stone-200 text-stone-800',
    },
    {
        name: 'الاحترافية',
        tagline: 'المثالية لمكاتب المحاماة المتنامية',
        monthlyPrice: 156,
        annualPrice: 1560,
        annualSavings: 'توفير 312 ج.م سنوياً (شهرين مجاناً)',
        badge: 'الأكثر شعبية 🚀',
        highlight: true,
        features: [
            { text: '10 مستخدمين (فريق عمل)', included: true },
            { text: '200 موكل', included: true },
            { text: '3,000 قضية', included: true },
            { text: 'إدارة المهام', included: true },
            { text: 'إدارة الفوترة والأتعاب', included: false },
            { text: 'إدارة المستندات والتوثيق', included: false },
            { text: 'بوابة الموكلين', included: false },
        ],
        buttonText: 'اشترك في الخطة الاحترافية',
        buttonClass: 'bg-red-700 hover:bg-red-800 text-white shadow-xl shadow-red-700/20',
    },
    {
        name: 'المؤسسات',
        tagline: 'لمكاتب وشركات المحاماة الكبيرة',
        monthlyPrice: 416,
        annualPrice: 4160,
        annualSavings: 'توفير 832 ج.م سنوياً (شهرين مجاناً)',
        badge: 'للمكاتب الكبيرة 💼',
        highlight: false,
        features: [
            { text: '50 مستخدم (فريق كامل)', included: true },
            { text: '1,000 موكل', included: true },
            { text: '20,000 قضية', included: true },
            { text: 'إدارة المهام', included: true },
            { text: 'إدارة الفوترة والأتعاب', included: true },
            { text: 'إدارة المستندات والتوثيق', included: true },
            { text: 'بوابة الموكلين', included: true },
        ],
        buttonText: 'اشترك في خطة المؤسسات',
        buttonClass: 'bg-stone-900 hover:bg-stone-800 text-white shadow-lg',
    },
];

const faqs = [
    {
        q: 'هل يمكنني الترقية أو تغيير الخطة في أي وقت؟',
        a: 'نعم، يمكنك ترقية خطتك أو تغيير فترة الفوترة في أي وقت من خلال لوحة التحكم وسيطبق الفرق تلقائياً.',
    },
    {
        q: 'ما هي طرق الدفع المتاحة؟',
        a: 'ندعم كافة طرق الدفع الإلكتروني (البطاقات البنكية، فوري، المحافظ الإلكترونية) بالإضافة للتحويلات البنكية.',
    },
    {
        q: 'هل توجد فترة تجريبية مجانية؟',
        a: 'نعم! نمنح جميع المكاتب فترة تجريبية مجانية لمدة 15 يوماً كاملة لكافة المميزات بدون الحاجة لإدخال بطاقة ائتمان.',
    },
    {
        q: 'هل بيانات مكتبي وموكلي آمنة؟',
        a: 'بالتأكيد. نعتمد معايير تشفير متقدمة لعزل بيانات كل مكتب بالكامل مع نسخ احتياطي يومي تشفيري.',
    },
];
</script>

<template>
    <Head title="خطط الأسعار والاشتراكات - منصة جلسات" />

    <div class="min-h-screen bg-stone-50 text-stone-900 font-sans selection:bg-red-600 selection:text-white dir-rtl">
        <!-- Top Red Accent Header Line -->
        <div class="fixed top-0 left-0 right-0 h-1.5 bg-red-700 z-50"></div>

        <!-- Navbar -->
        <nav class="fixed w-full z-40 bg-white/90 backdrop-blur-md border-b border-stone-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <Link href="/" class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-2xl bg-red-700 flex items-center justify-center text-white shadow-lg shadow-red-700/20">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <span class="text-2xl font-black text-stone-800 tracking-tight">جلسات</span>
                    </Link>

                    <div class="hidden md:flex items-center gap-8 text-sm font-bold">
                        <Link href="/" class="text-stone-500 hover:text-red-700 transition">الرئيسية</Link>
                        <Link href="/#features" class="text-stone-500 hover:text-red-700 transition">المميزات</Link>
                        <Link href="/pricing" class="text-red-700 font-black">خطط الأسعار</Link>
                    </div>

                    <div class="flex items-center gap-3">
                        <Link href="/login" class="text-stone-600 hover:text-stone-900 font-bold text-sm px-4 py-2">تسجيل الدخول</Link>
                        <Link href="/register" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl shadow-lg shadow-red-700/20 text-sm transition">
                            🚀 تجربة مجانية (15 يوم)
                        </Link>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Main Header -->
        <main class="pt-36 pb-20">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-50 border border-red-100 text-red-700 text-xs font-bold mb-6">
                    💎 أسعار واضحة وشفافة بدون مصاريف خفية
                </div>
                <h1 class="text-4xl sm:text-5xl lg:text-6xl font-black text-stone-900 tracking-tight leading-tight">
                    اختر الخطة المناسبة <br>
                    <span class="text-red-700">لحجم مكتبك وتطلعاتك</span>
                </h1>
                <p class="mt-4 text-lg text-stone-500 max-w-2xl mx-auto font-medium">
                    انضم لـ مئات مكاتب المحاماة التي تثق في منصة جلسات لإدارة أعمالها اليومية بكل سلاسة.
                </p>

                <!-- Billing Toggle Switch -->
                <div class="mt-10 inline-flex items-center gap-3 p-1.5 bg-stone-200/70 rounded-2xl border border-stone-300/60">
                    <button
                        @click="isAnnual = false"
                        class="px-6 py-2.5 rounded-xl font-black text-sm transition-all duration-300 cursor-pointer"
                        :class="!isAnnual ? 'bg-white text-stone-900 shadow-md' : 'text-stone-600 hover:text-stone-900'"
                    >
                        دفع شهري
                    </button>
                    <button
                        @click="isAnnual = true"
                        class="px-6 py-2.5 rounded-xl font-black text-sm transition-all duration-300 flex items-center gap-2 cursor-pointer"
                        :class="isAnnual ? 'bg-red-700 text-white shadow-lg shadow-red-700/20' : 'text-stone-600 hover:text-stone-900'"
                    >
                        <span>دفع سنوي</span>
                        <span class="px-2 py-0.5 text-[10px] font-black rounded-full" :class="isAnnual ? 'bg-white text-red-800' : 'bg-red-100 text-red-700'">
                            شهرين مجاناً 🔥
                        </span>
                    </button>
                </div>
            </div>

            <!-- Pricing Cards Grid -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-14">
                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-stretch">
                    <div
                        v-for="(plan, index) in plans"
                        :key="index"
                        class="relative rounded-3xl bg-white p-8 border transition-all duration-300 flex flex-col justify-between"
                        :class="[
                            plan.highlight 
                                ? 'border-2 border-red-700 shadow-2xl shadow-red-900/10 scale-105 z-10' 
                                : 'border-stone-200 hover:border-stone-300 hover:shadow-xl'
                        ]"
                    >
                        <!-- Badge -->
                        <div v-if="plan.badge" class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-red-700 text-white text-xs font-black rounded-full shadow-md">
                            {{ plan.badge }}
                        </div>

                        <div>
                            <!-- Plan Name & Tagline -->
                            <div class="mb-6">
                                <h3 class="text-2xl font-black text-stone-900 mb-1">{{ plan.name }}</h3>
                                <p class="text-xs font-semibold text-stone-400">{{ plan.tagline }}</p>
                            </div>

                            <!-- Price Display -->
                            <div class="mb-8 p-4 rounded-2xl bg-stone-50 border border-stone-100">
                                <div v-if="!isAnnual">
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-4xl font-black text-stone-900">{{ plan.monthlyPrice }}</span>
                                        <span class="text-xs font-bold text-stone-500">ج.م / شهرياً</span>
                                    </div>
                                    <span class="text-[11px] text-stone-400 block mt-1">تُدفع شهرياً وتُجدد تلقائياً</span>
                                </div>
                                <div v-else>
                                    <div class="flex items-baseline gap-1">
                                        <span class="text-4xl font-black text-stone-900">{{ plan.annualPrice.toLocaleString() }}</span>
                                        <span class="text-xs font-bold text-stone-500">ج.م / سنوياً</span>
                                    </div>
                                    <span v-if="plan.annualSavings" class="text-[11px] font-bold text-emerald-700 block mt-1 bg-emerald-50 px-2 py-0.5 rounded-md w-fit border border-emerald-100">
                                        ✨ {{ plan.annualSavings }}
                                    </span>
                                    <span v-else class="text-[11px] text-stone-400 block mt-1">مجانية للأبد بدون رسوم</span>
                                </div>
                            </div>

                            <!-- Features List -->
                            <ul class="space-y-3.5 mb-8">
                                <li
                                    v-for="(feat, fIdx) in plan.features"
                                    :key="fIdx"
                                    class="flex items-center gap-3 text-xs font-bold"
                                    :class="feat.included ? 'text-stone-700' : 'text-stone-400 opacity-60 line-through'"
                                >
                                    <svg v-if="feat.included" class="w-5 h-5 text-red-700 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                    <svg v-else class="w-5 h-5 text-stone-300 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                                    <span>{{ feat.text }}</span>
                                </li>
                            </ul>
                        </div>

                        <!-- Action Button -->
                        <Link
                            href="/register"
                            class="w-full text-center py-4 px-6 font-black text-sm rounded-xl transition-all duration-300 cursor-pointer block"
                            :class="plan.buttonClass"
                        >
                            {{ plan.buttonText }}
                        </Link>
                    </div>
                </div>
            </div>

            <!-- Comparison Table -->
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 mt-24">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-black text-stone-900">جدول مقارنة المميزات التفصيلي</h2>
                    <p class="text-stone-500 text-sm mt-2 font-semibold">مقارنة دقيقة بين كافة الخطط المتاحة</p>
                </div>

                <div class="bg-white rounded-3xl border border-stone-200 overflow-hidden shadow-sm">
                    <table class="w-full text-right">
                        <thead>
                            <tr class="bg-stone-100/70 border-b border-stone-200 text-xs font-black text-stone-700 uppercase">
                                <th class="p-5">الميزة / الخاصية</th>
                                <th class="p-5 text-center">المجانية</th>
                                <th class="p-5 text-center text-red-700 bg-red-50/50">الاحترافية</th>
                                <th class="p-5 text-center">المؤسسات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 text-xs font-bold text-stone-700">
                            <tr>
                                <td class="p-5 font-extrabold text-stone-900">السعر الشهري</td>
                                <td class="p-5 text-center">0 ج.م</td>
                                <td class="p-5 text-center bg-red-50/20 text-red-700 font-black">156 ج.م</td>
                                <td class="p-5 text-center">416 ج.م</td>
                            </tr>
                            <tr>
                                <td class="p-5 font-extrabold text-stone-900">السعر السنوي</td>
                                <td class="p-5 text-center">0 ج.م</td>
                                <td class="p-5 text-center bg-red-50/20 text-red-700 font-black">1,560 ج.م</td>
                                <td class="p-5 text-center">4,160 ج.م</td>
                            </tr>
                            <tr>
                                <td class="p-5">عدد المستخدمين (فريق العمل)</td>
                                <td class="p-5 text-center">1 مستخدم</td>
                                <td class="p-5 text-center bg-red-50/20 text-red-700">10 مستخدمين</td>
                                <td class="p-5 text-center">50 مستخدم</td>
                            </tr>
                            <tr>
                                <td class="p-5">عدد الموكلين المسجلين</td>
                                <td class="p-5 text-center">10 موكلين</td>
                                <td class="p-5 text-center bg-red-50/20 text-red-700">200 موكل</td>
                                <td class="p-5 text-center">1,000 موكل</td>
                            </tr>
                            <tr>
                                <td class="p-5">عدد القضايا</td>
                                <td class="p-5 text-center">100 قضية</td>
                                <td class="p-5 text-center bg-red-50/20 text-red-700">3,000 قضية</td>
                                <td class="p-5 text-center">20,000 قضية</td>
                            </tr>
                            <tr>
                                <td class="p-5">إدارة المهام</td>
                                <td class="p-5 text-center text-stone-300">✕ غير شاملة</td>
                                <td class="p-5 text-center bg-red-50/20 text-emerald-700 font-extrabold">✓ شاملة</td>
                                <td class="p-5 text-center text-emerald-700 font-extrabold">✓ شاملة</td>
                            </tr>
                            <tr>
                                <td class="p-5">إدارة الفوترة والأتعاب</td>
                                <td class="p-5 text-center text-stone-300">✕ غير شاملة</td>
                                <td class="p-5 text-center bg-red-50/20 text-stone-300">✕ غير شاملة</td>
                                <td class="p-5 text-center text-emerald-700 font-extrabold">✓ شاملة</td>
                            </tr>
                            <tr>
                                <td class="p-5">إدارة المستندات والتوثيق</td>
                                <td class="p-5 text-center text-stone-300">✕ غير شاملة</td>
                                <td class="p-5 text-center bg-red-50/20 text-stone-300">✕ غير شاملة</td>
                                <td class="p-5 text-center text-emerald-700 font-extrabold">✓ شاملة</td>
                            </tr>
                            <tr>
                                <td class="p-5">بوابة الموكلين</td>
                                <td class="p-5 text-center text-stone-300">✕ غير شاملة</td>
                                <td class="p-5 text-center bg-red-50/20 text-stone-300">✕ غير شاملة</td>
                                <td class="p-5 text-center text-emerald-700 font-extrabold">✓ شاملة</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- FAQ Section -->
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mt-24">
                <div class="text-center mb-12">
                    <h2 class="text-3xl font-black text-stone-900">الأسئلة الشائعة</h2>
                    <p class="text-stone-500 text-sm mt-2 font-semibold">إجابات عن أكثر الاستفسارات شيوعاً حول خطط الأسعار</p>
                </div>

                <div class="space-y-4">
                    <div
                        v-for="(faq, idx) in faqs"
                        :key="idx"
                        class="bg-white rounded-2xl p-6 border border-stone-200 shadow-xs"
                    >
                        <h3 class="text-base font-black text-stone-900 mb-2">❓ {{ faq.q }}</h3>
                        <p class="text-stone-600 text-xs leading-relaxed font-semibold">{{ faq.a }}</p>
                    </div>
                </div>
            </div>

            <!-- Banner CTA -->
            <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 mt-24">
                <div class="bg-red-700 rounded-3xl p-10 md:p-14 text-center text-white shadow-2xl shadow-red-900/30">
                    <h2 class="text-3xl sm:text-4xl font-black mb-4">جاهز لبدء تنظيم إدارة مكتبك؟</h2>
                    <p class="text-red-100 text-base max-w-xl mx-auto mb-8 font-semibold">
                        أنشئ حساب مكتبك الآن واستمتع بفترة تجريبية مجانية مدتها 15 يوماً تجرّب فيها كافة المميزات.
                    </p>
                    <Link href="/register" class="px-8 py-4 bg-white text-red-800 font-black rounded-xl text-base shadow-xl hover:bg-stone-100 transition inline-block">
                        🚀 ابدأ التجربة المجانية الان (15 يوماً)
                    </Link>
                </div>
            </div>
        </main>

        <!-- Footer -->
        <footer class="border-t border-stone-200 py-10 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-red-700 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <span class="text-xl font-black text-stone-800">جلسات</span>
                </div>
                <p class="text-stone-400 text-xs font-semibold">
                    &copy; {{ new Date().getFullYear() }} جميع الحقوق محفوظة لبرنامج جلسات لإدارة مكاتب المحاماة.
                </p>
            </div>
        </footer>
    </div>
</template>
