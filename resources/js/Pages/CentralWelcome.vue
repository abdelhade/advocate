<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, onMounted } from 'vue';

const isVisible = ref(false);

onMounted(() => {
    isVisible.value = true;
});

const features = [
    {
        title: 'بوابة الموكلين',
        description: 'بوابة معزولة بالكامل تتيح للموكلين متابعة القضايا والمستندات الخاصة بهم ذاتياً بكل سهولة وأمان.',
        icon: 'M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4',
    },
    {
        title: 'إدارة القضايا الشاملة',
        description: 'تنظيم كافة القضايا وحالاتها، مواعيد الجلسات، والمستندات المرتبطة بشكل مركزي وفعّال لمكتب المحاماة.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        title: 'متابعة المستحقات المالية',
        description: 'تتبع دقيق للمدفوعات، والمبالغ المتبقية، وكشوفات الحساب لكل موكل بدقة متناهية.',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'أرشفة المراسلات',
        description: 'أرشفة إلكترونية للخطابات، وربطها برقم القيد والجهة والقضية المعنية مع حفظ آمن للوثائق.',
        icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
    },
    {
        title: 'التقارير والإحصائيات',
        description: 'تقارير تفصيلية ولوحة تحكم ذكية تمنحك رؤية شاملة لأداء مكتبك واتخاذ القرارات الصائبة.',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    },
    {
        title: 'أمان متعدد المستويات',
        description: 'عزل كامل للبيانات بين المكاتب مع صلاحيات متعددة المستويات وتشفير متقدم لحماية بياناتك.',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    }
];

const stats = [
    { value: '+500', label: 'مكتب محاماة' },
    { value: '+10K', label: 'قضية مُدارة' },
    { value: '99.9%', label: 'وقت التشغيل' },
    { value: '24/7', label: 'دعم فني' },
];
</script>

<template>
    <Head title="أدفوكيت - نظام إدارة مكاتب المحاماة" />

    <div class="min-h-screen bg-white text-stone-900 font-sans selection:bg-red-600 selection:text-white">

        <!-- Navbar -->
        <nav class="fixed w-full z-50 bg-white border-b border-stone-200">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-3">
                        <div class="w-11 h-11 rounded-xl bg-red-700 flex items-center justify-center text-white">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <span class="text-2xl font-bold text-stone-800">أدفوكيت</span>
                    </div>

                    <div class="hidden md:flex items-center gap-8">
                        <a href="#features" class="text-stone-500 hover:text-red-700 font-medium transition-colors duration-300">المميزات</a>
                        <a href="#stats" class="text-stone-500 hover:text-red-700 font-medium transition-colors duration-300">الإحصائيات</a>
                        <a href="#pricing" class="text-stone-500 hover:text-red-700 font-medium transition-colors duration-300">الأسعار</a>
                        <a href="#contact" class="px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white font-semibold rounded-xl transition-colors duration-300">تواصل معنا</a>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section -->
        <main class="relative pt-36 pb-24 lg:pt-48 lg:pb-36">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-red-50 border border-red-100 text-red-700 text-sm font-medium mb-10 transition-all duration-1000"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                >
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-red-600"></span>
                    </span>
                    نظام SaaS متعدد المستأجرين لإدارة مكاتب المحاماة
                </div>

                <!-- Heading -->
                <h1
                    class="text-5xl md:text-6xl lg:text-8xl font-black tracking-tight leading-[1.05] mb-8 transition-all duration-1000 delay-200"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-8': !isVisible }"
                >
                    <span class="block text-stone-900">مستقبل</span>
                    <span class="text-red-700">إدارة مكاتب المحاماة</span>
                    <span class="block text-stone-900 mt-2">يبدأ من هنا</span>
                </h1>

                <!-- Subtitle -->
                <p
                    class="mt-8 text-xl md:text-2xl text-stone-500 max-w-3xl mx-auto leading-relaxed transition-all duration-1000 delay-400"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-8': !isVisible }"
                >
                    منصة سحابية متطورة توفر لكل مكتب محاماة مساحة عمل مستقلة وآمنة، مع بوابة مخصصة للموكلين لمتابعة قضاياهم ومستحقاتهم بشفافية كاملة.
                </p>

                <!-- CTA Buttons -->
                <div
                    class="mt-12 flex flex-col sm:flex-row justify-center gap-4 transition-all duration-1000 delay-500"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-8': !isVisible }"
                >
                    <a href="/register" class="group px-8 py-4 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl shadow-lg shadow-red-700/15 transition-all duration-300 hover:-translate-y-1 text-lg flex items-center justify-center gap-2">
                        🚀 ابدأ فترتك التجريبية مجاناً (15 يوم)
                        <svg class="w-5 h-5 rtl:rotate-180 group-hover:-translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                    </a>
                    <a href="#features" class="px-8 py-4 bg-stone-100 hover:bg-stone-200 text-stone-700 font-bold rounded-xl transition-all duration-300 hover:-translate-y-1 text-lg flex items-center justify-center gap-2">
                        اكتشف المميزات
                    </a>
                </div>

                <!-- Dashboard Preview -->
                <div
                    class="mt-24 relative max-w-5xl mx-auto transition-all duration-1000 delay-700"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-12': !isVisible }"
                >
                    <div class="absolute inset-0 bg-white/80 z-10 top-[60%] pointer-events-none"></div>
                    <div class="rounded-2xl border border-stone-200 bg-white p-2 shadow-2xl shadow-stone-900/5">
                        <div class="rounded-xl overflow-hidden bg-stone-50 aspect-video relative">
                            <div class="absolute inset-0 p-6">
                                <!-- Top bar -->
                                <div class="flex items-center justify-between mb-6">
                                    <div class="flex items-center gap-3">
                                        <div class="w-3 h-3 rounded-full bg-red-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-amber-400"></div>
                                        <div class="w-3 h-3 rounded-full bg-green-400"></div>
                                    </div>
                                    <div class="h-8 w-64 bg-stone-200 rounded-lg"></div>
                                    <div class="flex gap-2">
                                        <div class="w-8 h-8 rounded-lg bg-stone-200"></div>
                                        <div class="w-8 h-8 rounded-lg bg-red-100"></div>
                                    </div>
                                </div>
                                <div class="grid grid-cols-12 gap-4 h-[calc(100%-4rem)]">
                                    <!-- Sidebar -->
                                    <div class="col-span-2 bg-white rounded-xl border border-stone-200 p-3">
                                        <div class="space-y-2">
                                            <div class="h-8 w-full bg-red-50 rounded-lg border border-red-100"></div>
                                            <div class="h-8 w-full bg-stone-100 rounded-lg"></div>
                                            <div class="h-8 w-full bg-stone-100 rounded-lg"></div>
                                            <div class="h-8 w-full bg-stone-100 rounded-lg"></div>
                                            <div class="h-8 w-full bg-stone-100 rounded-lg"></div>
                                        </div>
                                    </div>
                                    <!-- Main content -->
                                    <div class="col-span-10 space-y-4">
                                        <div class="grid grid-cols-4 gap-3">
                                            <div class="bg-white rounded-xl border border-stone-200 p-4">
                                                <div class="h-3 w-16 bg-stone-200 rounded mb-2"></div>
                                                <div class="h-6 w-12 bg-red-100 rounded"></div>
                                            </div>
                                            <div class="bg-white rounded-xl border border-stone-200 p-4">
                                                <div class="h-3 w-16 bg-stone-200 rounded mb-2"></div>
                                                <div class="h-6 w-12 bg-amber-100 rounded"></div>
                                            </div>
                                            <div class="bg-white rounded-xl border border-stone-200 p-4">
                                                <div class="h-3 w-16 bg-stone-200 rounded mb-2"></div>
                                                <div class="h-6 w-12 bg-stone-200 rounded"></div>
                                            </div>
                                            <div class="bg-white rounded-xl border border-stone-200 p-4">
                                                <div class="h-3 w-16 bg-stone-200 rounded mb-2"></div>
                                                <div class="h-6 w-12 bg-red-100 rounded"></div>
                                            </div>
                                        </div>
                                        <div class="bg-white rounded-xl border border-stone-200 flex-1 h-32">
                                            <div class="p-4 h-full flex items-end gap-2">
                                                <div class="flex-1 bg-red-100 rounded-t-lg" style="height: 60%"></div>
                                                <div class="flex-1 bg-red-200 rounded-t-lg" style="height: 80%"></div>
                                                <div class="flex-1 bg-red-100 rounded-t-lg" style="height: 45%"></div>
                                                <div class="flex-1 bg-red-300 rounded-t-lg" style="height: 90%"></div>
                                                <div class="flex-1 bg-red-100 rounded-t-lg" style="height: 55%"></div>
                                                <div class="flex-1 bg-red-200 rounded-t-lg" style="height: 70%"></div>
                                                <div class="flex-1 bg-red-400 rounded-t-lg" style="height: 95%"></div>
                                                <div class="flex-1 bg-red-100 rounded-t-lg" style="height: 50%"></div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>

        <!-- Stats Section -->
        <section id="stats" class="relative py-20 border-y border-stone-200 bg-stone-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div v-for="(stat, i) in stats" :key="i" class="text-center group">
                        <div class="text-4xl md:text-5xl font-black text-red-700 mb-2 group-hover:text-stone-800 transition-colors duration-500">
                            {{ stat.value }}
                        </div>
                        <div class="text-stone-500 font-medium text-lg">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Features Section -->
        <section id="features" class="relative py-24 bg-white">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-20">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-red-50 border border-red-100 text-red-700 text-sm font-medium mb-6">
                        مميزات النظام
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-stone-900 tracking-tight leading-tight">
                        كل ما تحتاجه لإدارة <br>
                        <span class="text-red-700">مكتبك بنجاح</span>
                    </h2>
                    <p class="mt-6 text-xl text-stone-500 leading-relaxed">
                        صُمم النظام لتلبية احتياجات مكاتب المحاماة بكفاءة، مع التركيز على سهولة الاستخدام والأمان
                    </p>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="group relative bg-white rounded-2xl p-8 hover:shadow-xl hover:shadow-stone-200/50 transition-all duration-500 border border-stone-200 hover:border-red-200 overflow-hidden"
                    >
                        <!-- Top accent line -->
                        <div class="absolute top-0 left-0 w-full h-1 bg-red-700 transform scale-x-0 group-hover:scale-x-100 transition-transform duration-500 origin-right"></div>

                        <div class="relative z-10">
                            <div class="w-14 h-14 rounded-2xl bg-red-700 flex items-center justify-center mb-6">
                                <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-stone-900 mb-3 group-hover:text-red-700 transition-colors duration-300">{{ feature.title }}</h3>
                            <p class="text-stone-500 text-base leading-relaxed">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="relative py-24 bg-stone-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="text-center max-w-3xl mx-auto mb-20">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 rounded-full bg-stone-200 border border-stone-300 text-stone-700 text-sm font-medium mb-6">
                        خطط الأسعار
                    </div>
                    <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-stone-900 tracking-tight leading-tight">
                        خطط مرنة تناسب <br>
                        <span class="text-red-700">كل مكتب محاماة</span>
                    </h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto">
                    <!-- Basic Plan -->
                    <div class="relative bg-white rounded-2xl p-8 border border-stone-200 hover:border-stone-300 transition-all duration-300 hover:shadow-lg">
                        <h3 class="text-lg font-semibold text-stone-500 mb-2">الأساسية</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-black text-stone-900">199</span>
                            <span class="text-stone-400">ر.س / شهرياً</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-stone-600">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                حتى 50 قضية
                            </li>
                            <li class="flex items-center gap-3 text-stone-600">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                3 مستخدمين
                            </li>
                            <li class="flex items-center gap-3 text-stone-600">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                بوابة الموكلين
                            </li>
                        </ul>
                        <a href="#contact" class="block w-full text-center px-6 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold rounded-xl transition-colors duration-300">
                            ابدأ الآن
                        </a>
                    </div>

                    <!-- Pro Plan -->
                    <div class="relative bg-white rounded-2xl p-8 border-2 border-red-700 hover:shadow-xl transition-all duration-300 scale-[1.02]">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-red-700 text-white text-sm font-bold rounded-full">
                            الأكثر طلباً
                        </div>
                        <h3 class="text-lg font-semibold text-red-700 mb-2">الاحترافية</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-black text-stone-900">499</span>
                            <span class="text-stone-400">ر.س / شهرياً</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-stone-700">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                قضايا غير محدودة
                            </li>
                            <li class="flex items-center gap-3 text-stone-700">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                10 مستخدمين
                            </li>
                            <li class="flex items-center gap-3 text-stone-700">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                بوابة الموكلين + المالية
                            </li>
                            <li class="flex items-center gap-3 text-stone-700">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                التقارير المتقدمة
                            </li>
                        </ul>
                        <a href="#contact" class="block w-full text-center px-6 py-3 bg-red-700 hover:bg-red-800 text-white font-bold rounded-xl shadow-lg shadow-red-700/15 transition-colors duration-300">
                            ابدأ الآن
                        </a>
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="relative bg-white rounded-2xl p-8 border border-stone-200 hover:border-stone-300 transition-all duration-300 hover:shadow-lg">
                        <h3 class="text-lg font-semibold text-stone-500 mb-2">المؤسسات</h3>
                        <div class="flex items-baseline gap-1 mb-6">
                            <span class="text-4xl font-black text-stone-900">مخصص</span>
                        </div>
                        <ul class="space-y-3 mb-8">
                            <li class="flex items-center gap-3 text-stone-600">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                كل مميزات الاحترافية
                            </li>
                            <li class="flex items-center gap-3 text-stone-600">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                مستخدمين غير محدودين
                            </li>
                            <li class="flex items-center gap-3 text-stone-600">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                سيرفر مخصص
                            </li>
                            <li class="flex items-center gap-3 text-stone-600">
                                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                دعم فني مميز 24/7
                            </li>
                        </ul>
                        <a href="#contact" class="block w-full text-center px-6 py-3 bg-stone-100 hover:bg-stone-200 text-stone-700 font-semibold rounded-xl transition-colors duration-300">
                            تواصل معنا
                        </a>
                    </div>
                </div>
            </div>
        </section>

        <!-- CTA Section -->
        <section id="contact" class="relative py-24 bg-white">
            <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="relative bg-red-700 rounded-3xl p-12 md:p-16 overflow-hidden text-center">
                    <div class="relative z-10">
                        <h2 class="text-3xl sm:text-4xl lg:text-5xl font-black text-white mb-6 leading-tight">
                            جاهز للارتقاء بإدارة مكتبك؟
                        </h2>
                        <p class="text-red-100 text-xl mb-10 max-w-2xl mx-auto leading-relaxed">
                            انضم إلى مكاتب المحاماة الرائدة التي تعتمد على منصتنا لتنظيم أعمالها وتحسين تجربة موكليها.
                        </p>
                        <div class="flex flex-col sm:flex-row justify-center gap-4">
                            <a href="mailto:info@advocate.sa" class="px-8 py-4 bg-white text-red-800 font-bold rounded-xl shadow-xl hover:shadow-2xl transition-all duration-300 hover:-translate-y-1 text-lg">
                                تواصل معنا الآن
                            </a>
                            <a href="https://wa.me/" class="px-8 py-4 bg-red-800 hover:bg-red-900 text-white font-bold rounded-xl border border-red-600 transition-all duration-300 hover:-translate-y-1 text-lg flex items-center justify-center gap-2">
                                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                                واتساب
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-stone-200 py-12 bg-stone-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-2">
                    <div class="w-9 h-9 rounded-xl bg-red-700 flex items-center justify-center text-white">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <span class="text-xl font-bold text-stone-800">أدفوكيت</span>
                </div>
                <p class="text-stone-400 text-sm">
                    &copy; {{ new Date().getFullYear() }} جميع الحقوق محفوظة لبرنامج أدفوكيت لإدارة مكاتب المحاماة.
                </p>
                <div class="flex gap-4">
                    <a href="#" class="text-stone-400 hover:text-red-700 transition-colors duration-300">
                        <span class="sr-only">تويتر</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.29 20.251c7.547 0 11.675-6.253 11.675-11.675 0-.178 0-.355-.012-.53A8.348 8.348 0 0022 5.92a8.19 8.19 0 01-2.357.646 4.118 4.118 0 001.804-2.27 8.224 8.224 0 01-2.605.996 4.107 4.107 0 00-6.993 3.743 11.65 11.65 0 01-8.457-4.287 4.106 4.106 0 001.27 5.477A4.072 4.072 0 012.8 9.713v.052a4.105 4.105 0 003.292 4.022 4.095 4.095 0 01-1.853.07 4.108 4.108 0 003.834 2.85A8.233 8.233 0 012 18.407a11.616 11.616 0 006.29 1.84"/></svg>
                    </a>
                    <a href="#" class="text-stone-400 hover:text-red-700 transition-colors duration-300">
                        <span class="sr-only">لينكد إن</span>
                        <svg class="h-5 w-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z" clip-rule="evenodd"/></svg>
                    </a>
                </div>
            </div>
        </footer>
    </div>
</template>
