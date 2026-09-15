<script setup>
import { Head, Link, usePage } from '@inertiajs/vue3';
import { ref, onMounted, computed } from 'vue';

const props = defineProps({
    stats: Array,
    realCounts: Object,
    tenantDashboardUrl: String,
});

const page = usePage();
const user = computed(() => page.props.auth?.user);

const isVisible = ref(false);
const isAnnualPricing = ref(true);
const activeTab = ref('all');
const searchQuery = ref('');

onMounted(() => {
    isVisible.value = true;
});

// Interactive Grid Demo Data (AG Grid style live interactive component)
const demoCases = ref([
    { id: 'CASE-2026-901', title: 'شركة الأفق ضد المؤسسة الوطنية', client: 'شركة الأفق القابضة', court: 'محكمة الاستئناف التجاري - الرياض', date: '2026-09-18', status: 'جلسة قادمة', statusClass: 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30', amount: '150,000 ' },
    { id: 'CASE-2026-842', title: 'دعوى تعويض عقاري رقم 402', client: 'د. خالد بن عبدالمحسن', court: 'المحكمة العامة - جدة', date: '2026-09-22', status: 'قيد النظر', statusClass: 'bg-amber-500/10 text-amber-400 border-amber-500/30', amount: '85,000 ' },
    { id: 'CASE-2026-731', title: 'منازعة تنفيذية رقم 119', client: 'مجموعة المدى للمقاولات', court: 'محكمة التنفيذ - الدمام', date: '2026-09-15', status: 'مستعجل', statusClass: 'bg-red-500/10 text-red-400 border-red-500/30', amount: '320,000 ' },
    { id: 'CASE-2026-610', title: 'إثبات ملكية وعقد تمليك', client: 'الشيخ عبدالمجيد منصور', court: 'محكمة الأحوال - المدينة', date: '2026-10-05', status: 'محجوزة للحكم', statusClass: 'bg-blue-500/10 text-blue-400 border-blue-500/30', amount: '45,000 ' },
    { id: 'CASE-2026-509', title: 'تحصيل مستحقات توريد', client: 'مصنع الشرق للأغذية', court: 'المحكمة التجارية - الخبر', date: '2026-10-12', status: 'قيد النظر', statusClass: 'bg-amber-500/10 text-amber-400 border-amber-500/30', amount: '210,000 ' },
]);

const filteredCases = computed(() => {
    return demoCases.value.filter(c => {
        const matchesTab = activeTab.value === 'all' || 
            (activeTab.value === 'upcoming' && c.status === 'جلسة قادمة') ||
            (activeTab.value === 'urgent' && c.status === 'مستعجل');
        
        const q = searchQuery.value.toLowerCase().trim();
        const matchesQuery = !q || c.title.toLowerCase().includes(q) || c.client.toLowerCase().includes(q) || c.id.toLowerCase().includes(q);

        return matchesTab && matchesQuery;
    });
});

const features = [
    {
        title: 'بوابة الموكلين التفاعلية',
        description: 'بوابة مستقيمة تتيح للموكلين متابعة سير القضايا، المستندات، وكشوف الحساب مباشرة بكل أمان.',
        icon: 'M12 11c0 3.517-1.009 6.799-2.753 9.571m-3.44-2.04l.054-.09A13.916 13.916 0 008 11a4 4 0 118 0c0 1.017-.07 2.019-.203 3m-2.118 6.844A21.88 21.88 0 0015.171 17m3.839 1.132c.645-2.266.99-4.659.99-7.132A8 8 0 008 4.07M3 15.364c.64-1.319 1-2.8 1-4.364 0-1.457.39-2.823 1.07-4',
    },
    {
        title: 'إدارة القضايا ومواعيد الجلسات',
        description: 'تتبع شامل لجميع ملفات القضايا، منطوق الجلسات، التواريخ المقبولة، والتنبيهات المسبقة.',
        icon: 'M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
    },
    {
        title: 'الفوترة والمطالبات المالية',
        description: 'نظام محاسبي مالي مخصص للمحاماة لإصدار الفواتير، الدفعات، وتتبع الأتعاب والمصروفات.',
        icon: 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
    },
    {
        title: 'الأرشفة والوثائق الرقمية',
        description: 'حفظ آمن للخطابات، العقود، والملفات ذات العلاقة بكل قضية وموكل مع إمكانية البحث السريع.',
        icon: 'M5 8h14M5 8a2 2 0 110-4h14a2 2 0 110 4M5 8v10a2 2 0 002 2h10a2 2 0 002-2V8m-9 4h4',
    },
    {
        title: 'لوحة تحكم وتقارير فورية',
        description: 'رؤية لحظية وشاملة لأداء المكتب، إحصائيات الجلسات، والمتحصلات المالية لاتخاذ أفضل القرارات.',
        icon: 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
    },
    {
        title: 'عزل تام وتشفير عالي الأمان',
        description: 'بنية سحابية معزولة بكل مكتب محاماة (Multi-tenant) تضمن سرية مطلقة لكافة البيانات.',
        icon: 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z',
    }
];

const displayStats = computed(() => {
    if (props.stats && props.stats.length > 0) {
        return props.stats;
    }
    return [
        { value: '+1.5 ألف', label: 'مكتب محاماة' },
        { value: '+157 ألف', label: 'موكل مخدوم' },
        { value: '+15.7 مليون', label: 'قضية مُدارة' },
        { value: '+15.7 مليون', label: 'فاتورة ومطالبة' },
    ];
});
</script>

<template>
    <Head title="جلسات - المنصة السحابية المتقدمة لإدارة مكاتب المحاماة" />

    <div class="min-h-screen bg-slate-950 text-slate-100 font-sans selection:bg-red-600 selection:text-white dir-rtl" dir="rtl">

        <!-- Glowing Ambient Background Gradients -->
        <div class="fixed inset-0 pointer-events-none overflow-hidden z-0">
            <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[1000px] h-[500px] bg-red-900/20 blur-[140px] rounded-full"></div>
            <div class="absolute top-1/3 -right-40 w-[600px] h-[600px] bg-red-950/15 blur-[160px] rounded-full"></div>
            <div class="absolute bottom-0 -left-40 w-[600px] h-[600px] bg-slate-900/40 blur-[160px] rounded-full"></div>
        </div>

        <!-- Navbar (AG Grid Style Dark Glass Navigation) -->
        <nav class="fixed w-full z-50 bg-slate-950/80 backdrop-blur-md border-b border-slate-800/80">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between items-center h-20">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white shadow-lg shadow-red-900/30">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <span class="text-2xl font-black text-white tracking-wide">جلسات</span>
                        <span class="text-[11px] font-bold px-2 py-0.5 rounded-md bg-red-500/10 border border-red-500/20 text-red-400">SaaS</span>
                    </div>

                    <div class="hidden md:flex items-center gap-8 text-sm font-semibold">
                        <a href="#demo" class="text-slate-400 hover:text-white transition-colors duration-200">الجدول التفاعلي</a>
                        <a href="#features" class="text-slate-400 hover:text-white transition-colors duration-200">المميزات</a>
                        <a href="#stats" class="text-slate-400 hover:text-white transition-colors duration-200">الإحصائيات</a>
                        <a href="#pricing" class="text-slate-400 hover:text-white transition-colors duration-200">الأسعار</a>
                    </div>

                    <div class="flex items-center gap-3">
                        <template v-if="user">
                            <a :href="props.tenantDashboardUrl || '/dashboard'" class="px-5 py-2.5 bg-red-700 hover:bg-red-600 text-white font-bold rounded-xl transition-all duration-200 shadow-lg shadow-red-900/20 text-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                <span>{{ user.name }}</span>
                            </a>
                        </template>
                        <template v-else>
                            <Link href="/login" class="px-4 py-2 text-slate-300 hover:text-white font-bold rounded-xl transition-colors duration-200 text-sm">
                                تسجيل الدخول
                            </Link>
                            <Link href="/register" class="px-5 py-2.5 bg-red-700 hover:bg-red-600 text-white font-bold rounded-xl transition-all duration-200 shadow-lg shadow-red-900/30 text-sm">
                                تسجيل جديد
                            </Link>
                        </template>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Hero Section (AG Grid Dark Tech Aesthetic) -->
        <section class="relative pt-36 pb-20 lg:pt-48 lg:pb-32 overflow-hidden z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <!-- Badge -->
                <div
                    class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-slate-900/90 border border-slate-800 text-slate-300 text-xs font-semibold mb-8 transition-all duration-700"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                >
                    <span class="flex h-2 w-2 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                    </span>
                    <span class="text-slate-200 font-bold">جلسات v2.0</span>
                    <span class="text-slate-500">|</span>
                    <span class="text-red-400 font-semibold">إدارة مكاتب المحاماة كأنظمة سحابية متكاملة</span>
                </div>

                <!-- Headline -->
                <h1
                    class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-[1.15] mb-8 text-white transition-all duration-700 delay-100"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-6': !isVisible }"
                >
                    منصة <span class="bg-gradient-to-r from-red-500 via-red-600 to-amber-500 bg-clip-text text-transparent">جلسات</span> <br class="hidden sm:block" />
                    إدارة مكاتب المحاماة الذكية
                </h1>

                <!-- Subtitle -->
                <p
                    class="mt-6 text-lg sm:text-xl text-slate-400 max-w-3xl mx-auto leading-relaxed font-normal transition-all duration-700 delay-200"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-6': !isVisible }"
                >
                    إدارة الجلسات والقضايا والمستحقات المالية وبوابة الموكلين بمرونة كاملة وعزل تام لبيانات مكتبك.
                </p>

                <!-- CTA Buttons -->
                <div
                    class="mt-10 flex flex-col sm:flex-row justify-center gap-4 transition-all duration-700 delay-300"
                    :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-6': !isVisible }"
                >
                    <template v-if="user">
                        <a :href="props.tenantDashboardUrl || '/dashboard'" class="px-8 py-4 bg-gradient-to-r from-red-700 to-red-600 hover:from-red-600 hover:to-red-500 text-white font-bold rounded-2xl shadow-xl shadow-red-900/30 transition-all duration-200 hover:-translate-y-0.5 text-base flex items-center justify-center gap-2">
                            🏛️ الدخول لمكتبك
                            <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </a>
                    </template>
                    <template v-else>
                        <Link href="/register" class="px-8 py-4 bg-gradient-to-r from-red-700 to-red-600 hover:from-red-600 hover:to-red-500 text-white font-bold rounded-2xl shadow-xl shadow-red-900/30 transition-all duration-200 hover:-translate-y-0.5 text-base flex items-center justify-center gap-2">
                            🚀 ابدأ فترتك التجريبية مجاناً
                            <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                        </Link>
                    </template>
                    <a href="#demo" class="px-8 py-4 bg-slate-900 hover:bg-slate-800 text-slate-200 font-bold rounded-2xl border border-slate-800 transition-all duration-200 hover:-translate-y-0.5 text-base flex items-center justify-center gap-2">
                        تجربة الجدول التفاعلي
                    </a>
                </div>

                <!-- AG GRID STYLE INTERACTIVE DEMO SHOWCASE -->
                <div id="demo" class="mt-20 relative max-w-6xl mx-auto text-right">
                    <div class="rounded-3xl border border-slate-800 bg-slate-900/90 shadow-2xl shadow-slate-950/80 backdrop-blur-xl overflow-hidden">
                        
                        <!-- Grid Header Toolbar -->
                        <div class="px-6 py-4 border-b border-slate-800 bg-slate-950/60 flex flex-wrap justify-between items-center gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex gap-1.5">
                                    <div class="w-3 h-3 rounded-full bg-red-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-amber-500/80"></div>
                                    <div class="w-3 h-3 rounded-full bg-emerald-500/80"></div>
                                </div>
                                <span class="text-xs font-mono text-slate-400 mr-3">jalsat-grid-v2.0 // live_cases_view</span>
                            </div>

                            <!-- Filter Tabs -->
                            <div class="flex items-center gap-2 bg-slate-900 p-1 rounded-xl border border-slate-800 text-xs">
                                <button
                                    @click="activeTab = 'all'"
                                    class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                                    :class="activeTab === 'all' ? 'bg-red-700 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                >
                                    الكل ({{ demoCases.length }})
                                </button>
                                <button
                                    @click="activeTab = 'upcoming'"
                                    class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                                    :class="activeTab === 'upcoming' ? 'bg-red-700 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                >
                                    الجلسات القادمة
                                </button>
                                <button
                                    @click="activeTab = 'urgent'"
                                    class="px-3 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                                    :class="activeTab === 'urgent' ? 'bg-red-700 text-white shadow-sm' : 'text-slate-400 hover:text-white'"
                                >
                                    الحالات المستعجلة
                                </button>
                            </div>

                            <!-- Live Search Filter Input -->
                            <div class="relative w-full sm:w-64">
                                <input
                                    v-model="searchQuery"
                                    type="text"
                                    placeholder="بحث سريع برقم القضية أو الموكل..."
                                    class="w-full bg-slate-900 border border-slate-800 rounded-xl px-3.5 py-1.5 text-xs text-slate-200 placeholder-slate-500 focus:outline-none focus:border-red-600 transition"
                                />
                            </div>
                        </div>

                        <!-- Grid Table Component -->
                        <div class="overflow-x-auto">
                            <table class="w-full text-right border-collapse text-xs">
                                <thead>
                                    <tr class="border-b border-slate-800 bg-slate-950/40 text-slate-400 font-bold uppercase tracking-wider">
                                        <th class="py-3.5 px-4">رقم الملف</th>
                                        <th class="py-3.5 px-4">عنوان القضية</th>
                                        <th class="py-3.5 px-4">اسم الموكل</th>
                                        <th class="py-3.5 px-4">المحكمة المختصة</th>
                                        <th class="py-3.5 px-4">موعد الجلسة</th>
                                        <th class="py-3.5 px-4">حالة الملف</th>
                                        <th class="py-3.5 px-4 text-left">الأتعاب المقدرة</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-800/60 font-medium">
                                    <tr v-for="c in filteredCases" :key="c.id" class="hover:bg-slate-800/40 transition-colors duration-150">
                                        <td class="py-3.5 px-4 font-mono font-bold text-red-400">{{ c.id }}</td>
                                        <td class="py-3.5 px-4 font-bold text-slate-100">{{ c.title }}</td>
                                        <td class="py-3.5 px-4 text-slate-300">{{ c.client }}</td>
                                        <td class="py-3.5 px-4 text-slate-400">{{ c.court }}</td>
                                        <td class="py-3.5 px-4 font-mono text-slate-300">{{ c.date }}</td>
                                        <td class="py-3.5 px-4">
                                            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold border" :class="c.statusClass">
                                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                {{ c.status }}
                                            </span>
                                        </td>
                                        <td class="py-3.5 px-4 font-mono font-bold text-slate-100 text-left">{{ c.amount }}</td>
                                    </tr>
                                    <tr v-if="filteredCases.length === 0">
                                        <td colspan="7" class="py-8 text-center text-slate-500">لا توجد نتائج تطابق بحثك الحالية</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>

                        <!-- Footer Bar of Grid -->
                        <div class="px-6 py-3 border-t border-slate-800 bg-slate-950/80 flex justify-between items-center text-[11px] text-slate-400 font-mono">
                            <span>عرض {{ filteredCases.length }} من أصل {{ demoCases.length }} سجلاً</span>
                            <span class="text-emerald-400 flex items-center gap-1">
                                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                                التزامن لحظي ومحمي بالتشفير
                            </span>
                        </div>
                    </div>
                </div>

            </div>
        </section>

        <!-- Stats Counter Section (Real DB Metrics) -->
        <section id="stats" class="relative py-16 border-y border-slate-800/80 bg-slate-900/50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-8">
                    <div v-for="(stat, i) in displayStats" :key="i" class="text-center group p-6 rounded-2xl bg-slate-950/40 border border-slate-800/60 hover:border-red-900/40 transition-all duration-300">
                        <div class="text-4xl md:text-5xl font-black text-white mb-2 group-hover:text-red-500 transition-colors">
                            {{ stat.value }}
                        </div>
                        <div class="text-slate-400 font-semibold text-base">{{ stat.label }}</div>
                    </div>
                </div>
            </div>
        </section>

        <!-- Core Features Section -->
        <section id="features" class="relative py-24 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="max-w-3xl mx-auto mb-16">
                    <span class="px-3.5 py-1.5 rounded-full bg-red-500/10 border border-red-500/20 text-red-400 text-xs font-bold mb-4 inline-block">مميزات المنصة</span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">كل ما يحتاجه مكتب المحاماة الحديث</h2>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-right">
                    <div
                        v-for="(feature, index) in features"
                        :key="index"
                        class="p-8 rounded-3xl bg-slate-900/60 border border-slate-800 hover:border-red-600/40 transition-all duration-300 hover:-translate-y-1 group"
                    >
                        <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center mb-6 text-white shadow-lg shadow-red-900/20 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                            </svg>
                        </div>
                        <h3 class="text-xl font-bold text-white mb-3 group-hover:text-red-400 transition-colors">{{ feature.title }}</h3>
                        <p class="text-slate-400 text-sm leading-relaxed">{{ feature.description }}</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Pricing Section -->
        <section id="pricing" class="relative py-24 border-t border-slate-800/80 bg-slate-900/30 z-10">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                <div class="max-w-3xl mx-auto mb-16">
                    <span class="px-3.5 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-slate-300 text-xs font-bold mb-4 inline-block">خطط الاشتراك</span>
                    <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-8">خطط مرنة تناسب حجم مكتبك</h2>

                    <!-- Billing Toggle -->
                    <div class="inline-flex items-center gap-2 p-1.5 bg-slate-900 rounded-2xl border border-slate-800">
                        <button
                            @click="isAnnualPricing = false"
                            class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all cursor-pointer"
                            :class="!isAnnualPricing ? 'bg-slate-800 text-white shadow' : 'text-slate-400 hover:text-white'"
                        >
                            دفع شهري
                        </button>
                        <button
                            @click="isAnnualPricing = true"
                            class="px-5 py-2.5 rounded-xl font-bold text-xs transition-all flex items-center gap-2 cursor-pointer"
                            :class="isAnnualPricing ? 'bg-red-700 text-white shadow' : 'text-slate-400 hover:text-white'"
                        >
                            <span>دفع سنوي</span>
                            <span class="px-2 py-0.5 text-[10px] font-black rounded-md" :class="isAnnualPricing ? 'bg-white text-red-900' : 'bg-red-500/20 text-red-400'">
                                توفير حتى 40%
                            </span>
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto text-right items-stretch">
                    <!-- Free Plan -->
                    <div class="p-8 rounded-3xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">المجانية</h3>
                            <p class="text-xs text-slate-400 mb-6">للتجربة والمحامين المستقلين</p>
                            <div class="mb-6 p-4 rounded-2xl bg-slate-950 border border-slate-800 text-right">
                                <span class="text-4xl font-black text-white">0</span>
                                <span class="text-xs text-slate-400 mr-1">ج.م / {{ isAnnualPricing ? 'سنوياً' : 'شهرياً' }}</span>
                            </div>
                            <ul class="space-y-3 mb-8 text-xs font-semibold text-slate-300">
                                <li class="flex items-center gap-2.5">✓ 1 مستخدم (محامي واحد)</li>
                                <li class="flex items-center gap-2.5">✓ 10 موكلين</li>
                                <li class="flex items-center gap-2.5 text-slate-500 line-through">إدارة الفوترة والمطالبات</li>
                                <li class="flex items-center gap-2.5 text-slate-500 line-through">تتبع المصروفات والأتعاب</li>
                            </ul>
                        </div>
                        <Link href="/register" class="w-full text-center px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition">
                            ابدأ مجاناً
                        </Link>
                    </div>

                    <!-- Pro Plan -->
                    <div class="p-8 rounded-3xl bg-slate-900 border-2 border-red-600 flex flex-col justify-between shadow-2xl shadow-red-950/50 relative">
                        <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-red-600 text-white text-[11px] font-black rounded-full shadow">
                            الخيار الأكثر طلباً 🚀
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">الاحترافية</h3>
                            <p class="text-xs text-slate-400 mb-6">للمكاتب المتوسطة والمتنامية</p>
                            <div class="mb-6 p-4 rounded-2xl bg-slate-950 border border-slate-800 text-right">
                                <span class="text-4xl font-black text-white">{{ isAnnualPricing ? '5,000' : '650' }}</span>
                                <span class="text-xs text-slate-400 mr-1">ج.م / {{ isAnnualPricing ? 'سنوياً' : 'شهرياً' }}</span>
                            </div>
                            <ul class="space-y-3 mb-8 text-xs font-semibold text-slate-200">
                                <li class="flex items-center gap-2.5 text-red-400 font-bold">✓ 5 مستخدمين</li>
                                <li class="flex items-center gap-2.5">✓ 100 موكل</li>
                                <li class="flex items-center gap-2.5">✓ كامل خصائص الفوترة والمطالبات</li>
                                <li class="flex items-center gap-2.5">✓ تسجيل وتتبع المصروفات</li>
                            </ul>
                        </div>
                        <Link href="/register" class="w-full text-center px-6 py-3.5 bg-red-700 hover:bg-red-600 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-red-900/30">
                            اشترك الآن
                        </Link>
                    </div>

                    <!-- Enterprise Plan -->
                    <div class="p-8 rounded-3xl bg-slate-900/70 border border-slate-800 flex flex-col justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-white mb-2">المؤسسات</h3>
                            <p class="text-xs text-slate-400 mb-6">للكيانات والمكاتب الكبرى</p>
                            <div class="mb-6 p-4 rounded-2xl bg-slate-950 border border-slate-800 text-right">
                                <span class="text-4xl font-black text-white">{{ isAnnualPricing ? '9,000' : '1,250' }}</span>
                                <span class="text-xs text-slate-400 mr-1">ج.م / {{ isAnnualPricing ? 'سنوياً' : 'شهرياً' }}</span>
                            </div>
                            <ul class="space-y-3 mb-8 text-xs font-semibold text-slate-300">
                                <li class="flex items-center gap-2.5 text-red-400 font-bold">✓ 25 مستخدم</li>
                                <li class="flex items-center gap-2.5">✓ 1,000 موكل</li>
                                <li class="flex items-center gap-2.5">✓ الفوترة والمطالبات والتقارير</li>
                                <li class="flex items-center gap-2.5 text-red-400 font-bold">✓ دعم فني متواصل 24/7</li>
                            </ul>
                        </div>
                        <Link href="/register" class="w-full text-center px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition">
                            اشترك الآن
                        </Link>
                    </div>
                </div>
            </div>
        </section>

        <!-- Footer -->
        <footer class="border-t border-slate-800/80 py-12 bg-slate-950 text-slate-400 text-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-lg bg-red-700 flex items-center justify-center text-white font-bold">
                        ج
                    </div>
                    <span class="text-lg font-bold text-white">جلسات</span>
                </div>
                <p class="text-xs text-slate-500">
                    &copy; {{ new Date().getFullYear() }} منصة جلسات لإدارة مكاتب المحاماة. جميع الحقوق محفوظة.
                </p>
            </div>
        </footer>

    </div>
</template>
