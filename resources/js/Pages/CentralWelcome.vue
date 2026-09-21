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

// Real Sessions Schedule Table Data (جدول الجلسات القضائية الحقيقي)
const realSessions = ref([
    {
        id: 'جلسة 1447 / 2026',
        caseNo: '1447/2026 تجاري',
        title: 'شركة الأفق ضد المؤسسة الوطنية للتوريدات',
        client: 'شركة الأفق القابضة',
        court: 'محكمة استئناف القاهرة - الدائرة 7 تجاري',
        date: '2026-09-20',
        time: '09:30 صباحاً',
        requirement: 'مرافعة ختامية وتقديم المذكرات',
        status: 'جلسات هذا الأسبوع',
        statusBadge: 'قادمة - هذا الأسبوع',
        statusClass: 'bg-emerald-500/15 text-emerald-300 border-emerald-500/30',
        judgeRoom: 'قاعة 4 - الدور الثاني'
    },
    {
        id: 'جلسة 892 / 2026',
        caseNo: '892/2026 ملكية',
        title: 'دعوى منازعة علامة تجارية وحماية ملكية',
        client: 'د. خالد بن عبدالمحسن',
        court: 'المحكمة الاقتصادية - الدائرة 3 استئنافي',
        date: '2026-09-21',
        time: '10:00 صباحاً',
        requirement: 'حضور الخبير والاطلاع على التقرير',
        status: 'قيد النظر',
        statusBadge: 'قيد النظر',
        statusClass: 'bg-amber-500/15 text-amber-300 border-amber-500/30',
        judgeRoom: 'قاعة 2 - الدور الأول'
    },
    {
        id: 'جلسة 3105 / 2026',
        caseNo: '3105/2026 إداري',
        title: 'طعن على قرار إداري وإلغاء ترخيص تشغيل',
        client: 'مجموعة المدى للمقاولات',
        court: 'محكمة مجلس الدولة - القضاء الإداري 2',
        date: '2026-09-22',
        time: '11:30 صباحاً',
        requirement: 'تقديم الأصل وصحيفة الطعن للمحكمة',
        status: 'مستعجل',
        statusBadge: 'عاجل جداً',
        statusClass: 'bg-rose-500/15 text-rose-300 border-rose-500/30',
        judgeRoom: 'قاعة 1 - الدور الأرضي'
    },
    {
        id: 'جلسة 512 / 2026',
        caseNo: '512/2026 مدني',
        title: 'دعوى صحة ونفاذ عقد بيع عقاري وتثبيت ملكية',
        client: 'الشيخ عبدالمجيد منصور',
        court: 'محكمة شمال القاهرة - مدني كلي 14',
        date: '2026-09-25',
        time: '09:00 صباحاً',
        requirement: 'صدور القرار ومحجوزة للنطق بالحكم',
        status: 'محجوزة للحكم',
        statusBadge: 'محجوزة للحكم',
        statusClass: 'bg-blue-400/15 text-blue-300 border-blue-400/30',
        judgeRoom: 'قاعة 6 - الدور الثالث'
    },
    {
        id: 'جلسة 1109 / 2026',
        caseNo: '1109/2026 عمالي',
        title: 'استئناف حكم تعويض ونزاع مستحقات عمالية',
        client: 'مصنع الشرق للأغذية',
        court: 'محكمة استئناف الإسكندرية - الدائرة 5',
        date: '2026-09-28',
        time: '10:30 صباحاً',
        requirement: 'سماع شهود النفي وإعادة المرافعة',
        status: 'مؤجلة',
        statusBadge: 'مؤجلة للاطلاع',
        statusClass: 'bg-purple-500/15 text-purple-300 border-purple-500/30',
        judgeRoom: 'قاعة 3 - الدور الثاني'
    },
    {
        id: 'جلسة 670 / 2026',
        caseNo: '670/2026 أحوال',
        title: 'دعوى قسمة تركة وحصر إرث شرعي',
        client: 'أسرة المغفور له طارق عبدالعزيز',
        court: 'محكمة جنوب الجيزة - الأحوال الشخصية',
        date: '2026-10-02',
        time: '11:00 صباحاً',
        requirement: 'تقديم إعلام الوراثة ومستندات الملكية',
        status: 'قيد النظر',
        statusBadge: 'قيد التحضير',
        statusClass: 'bg-sky-500/15 text-sky-300 border-sky-500/30',
        judgeRoom: 'قاعة 5 - الدور الأول'
    }
]);

const filteredSessions = computed(() => {
    return realSessions.value.filter(s => {
        const matchesTab = activeTab.value === 'all' || 
            (activeTab.value === 'week' && s.status === 'جلسات هذا الأسبوع') ||
            (activeTab.value === 'judgment' && s.status === 'محجوزة للحكم') ||
            (activeTab.value === 'urgent' && s.status === 'مستعجل');
        
        const q = searchQuery.value.toLowerCase().trim();
        const matchesQuery = !q || 
            s.title.toLowerCase().includes(q) || 
            s.client.toLowerCase().includes(q) || 
            s.court.toLowerCase().includes(q) || 
            s.caseNo.toLowerCase().includes(q) || 
            s.id.toLowerCase().includes(q);

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
        description: 'بنية سحابية معزولة بكل مكتب محاماة تضمن سرية مطلقة لكافة البيانات والمستندات.',
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
    <Head title="جلسات - المنصة المتقدمة لإدارة مكاتب المحاماة" />

    <div class="min-h-screen font-sans selection:bg-blue-600 selection:text-white dir-rtl" dir="rtl">

        <!-- ========================================== -->
        <!-- TOP HALF: COBALT BLUE THEME (أزرق كوبالت)  -->
        <!-- ========================================== -->
        <div class="relative bg-gradient-to-b from-[#06152e] via-[#0c274c] to-[#091a34] text-white border-b-4 border-blue-600/40 shadow-2xl overflow-hidden">
            
            <!-- Glowing Cobalt Ambient Background Blur -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
                <div class="absolute -top-32 left-1/2 -translate-x-1/2 w-[1100px] h-[550px] bg-blue-600/25 blur-[150px] rounded-full"></div>
                <div class="absolute top-1/4 -right-20 w-[600px] h-[600px] bg-indigo-600/20 blur-[170px] rounded-full"></div>
                <div class="absolute bottom-0 -left-20 w-[600px] h-[600px] bg-sky-600/15 blur-[170px] rounded-full"></div>
            </div>

            <!-- Navbar -->
            <nav class="relative z-50 bg-[#06152e]/85 backdrop-blur-md border-b border-blue-800/40">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center h-20">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-blue-700 flex items-center justify-center text-white shadow-lg shadow-blue-900/50">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                            </div>
                            <span class="text-2xl font-black text-white tracking-wide">جلسات</span>
                        </div>

                        <div class="hidden md:flex items-center gap-8 text-sm font-semibold">
                            <a href="#sessions-table" class="text-blue-200 hover:text-white transition-colors duration-200">جدول الجلسات</a>
                            <a href="#features" class="text-blue-200 hover:text-white transition-colors duration-200">المميزات</a>
                            <a href="#stats" class="text-blue-200 hover:text-white transition-colors duration-200">الإحصائيات</a>
                            <a href="#pricing" class="text-blue-200 hover:text-white transition-colors duration-200">الأسعار</a>
                        </div>

                        <div class="flex items-center gap-3">
                            <template v-if="user">
                                <a :href="props.tenantDashboardUrl || '/dashboard'" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all duration-200 shadow-lg shadow-blue-900/30 text-sm flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"></path></svg>
                                    <span>{{ user.name }}</span>
                                </a>
                            </template>
                            <template v-else>
                                <Link href="/register" class="px-5 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl transition-all duration-200 shadow-lg shadow-blue-900/40 text-sm">
                                    تسجيل جديد
                                </Link>
                            </template>
                        </div>
                    </div>
                </div>
            </nav>

            <!-- Hero Section -->
            <section class="relative pt-24 pb-16 lg:pt-32 lg:pb-20 overflow-hidden z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    
                    <!-- Badge -->
                    <div
                        class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-blue-950/80 border border-blue-700/50 text-blue-200 text-xs font-semibold mb-8 transition-all duration-700"
                        :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-4': !isVisible }"
                    >
                        <span class="flex h-2 w-2 relative">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-blue-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-blue-400"></span>
                        </span>
                        <span class="text-white font-bold">منصة جلسات الرقمية</span>
                        <span class="text-blue-400">|</span>
                        <span class="text-blue-300 font-semibold">إدارة مكاتب المحاماة والجلسات القضائية الذكية</span>
                    </div>

                    <!-- Headline -->
                    <h1
                        class="text-4xl sm:text-6xl lg:text-7xl font-black tracking-tight leading-[1.15] mb-6 text-white transition-all duration-700 delay-100"
                        :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-6': !isVisible }"
                    >
                        إدارة الجلسات والقضايا <br class="hidden sm:block" />
                        <span class="bg-gradient-to-r from-blue-300 via-sky-200 to-indigo-200 bg-clip-text text-transparent">بأعلى درجات الدقة والاحترافية</span>
                    </h1>

                    <!-- Subtitle -->
                    <p
                        class="mt-4 text-lg sm:text-xl text-blue-100/90 max-w-3xl mx-auto leading-relaxed font-normal transition-all duration-700 delay-200"
                        :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-6': !isVisible }"
                    >
                        نظام متكامل لمتابعة جدول الجلسات اليومي، أوراق القضية، الأتعاب المالية، وتنظيم سير العمل بمكتب المحاماة بكل سهولة.
                    </p>

                    <!-- CTA Single Button -->
                    <div
                        class="mt-8 flex justify-center transition-all duration-700 delay-300"
                        :class="{ 'opacity-100 translate-y-0': isVisible, 'opacity-0 translate-y-6': !isVisible }"
                    >
                        <template v-if="user">
                            <a :href="props.tenantDashboardUrl || '/dashboard'" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-bold rounded-2xl shadow-xl shadow-blue-950/60 transition-all duration-200 hover:-translate-y-0.5 text-base flex items-center justify-center gap-2">
                                ⚖️ الانتقال إلى لوحة تحكم مكتبك
                                <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </a>
                        </template>
                        <template v-else>
                            <Link href="/register" class="px-8 py-4 bg-gradient-to-r from-blue-600 to-blue-700 hover:from-blue-500 hover:to-blue-600 text-white font-bold rounded-2xl shadow-xl shadow-blue-950/60 transition-all duration-200 hover:-translate-y-0.5 text-base flex items-center justify-center gap-2">
                                ابدأ فترتك التجريبية مجاناً
                                <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </Link>
                        </template>
                    </div>

                    <!-- REAL COURTS & SESSIONS SCHEDULE TABLE (جدول الجلسات الحقيقي) -->
                    <div id="sessions-table" class="mt-14 max-w-7xl mx-auto text-right">
                        
                        <div class="mb-4 flex items-center justify-between">
                  
                        </div>

                        <div class="rounded-2xl border border-blue-700/40 bg-[#0a1f3c]/90 shadow-2xl backdrop-blur-xl overflow-hidden">
                            
                            <!-- Table Toolbar -->
                            <div class="px-6 py-4 border-b border-blue-800/50 bg-[#071830] flex flex-wrap justify-between items-center gap-4">
                                
                                <!-- Filter Tabs -->
                                <div class="flex items-center gap-2 bg-[#091b36] p-1.5 rounded-xl border border-blue-800/50 text-xs">
                                    <button
                                        @click="activeTab = 'all'"
                                        class="px-3.5 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                                        :class="activeTab === 'all' ? 'bg-blue-600 text-white shadow-sm' : 'text-blue-200 hover:text-white'"
                                    >
                                        جميع الجلسات ({{ realSessions.length }})
                                    </button>
                                    <button
                                        @click="activeTab = 'week'"
                                        class="px-3.5 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                                        :class="activeTab === 'week' ? 'bg-blue-600 text-white shadow-sm' : 'text-blue-200 hover:text-white'"
                                    >
                                        جلسات هذا الأسبوع
                                    </button>
                                    <button
                                        @click="activeTab = 'judgment'"
                                        class="px-3.5 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                                        :class="activeTab === 'judgment' ? 'bg-blue-600 text-white shadow-sm' : 'text-blue-200 hover:text-white'"
                                    >
                                        محجوزة للحكم
                                    </button>
                                    <button
                                        @click="activeTab = 'urgent'"
                                        class="px-3.5 py-1.5 rounded-lg font-bold transition-all cursor-pointer"
                                        :class="activeTab === 'urgent' ? 'bg-blue-600 text-white shadow-sm' : 'text-blue-200 hover:text-white'"
                                    >
                                        الحالات العاجلة
                                    </button>
                                </div>

                                <!-- Live Search -->
                                <div class="relative w-full sm:w-72">
                                    <input
                                        v-model="searchQuery"
                                        type="text"
                                        placeholder="بحث برقم القضية، المحكمة، أو الموكل..."
                                        class="w-full bg-[#091b36] border border-blue-800/60 rounded-xl px-3.5 py-2 text-xs text-white placeholder-blue-300/50 focus:outline-none focus:border-blue-400 transition"
                                    />
                                </div>
                            </div>

                            <!-- Real Grid Table -->
                            <div class="overflow-x-auto">
                                <table class="w-full text-right border-collapse text-xs">
                                    <thead>
                                        <tr class="border-b border-blue-800/60 bg-[#06152b] text-blue-200 font-bold uppercase tracking-wider">
                                            <th class="py-4 px-4">رقم الجلسة / القضية</th>
                                            <th class="py-4 px-4">تاريخ وساعة الجلسة</th>
                                            <th class="py-4 px-4">المحكمة والدائرة</th>
                                            <th class="py-4 px-4">موضوع الدعوى / القضية</th>
                                            <th class="py-4 px-4">الموكل</th>
                                            <th class="py-4 px-4">المطلوب بالجلسة</th>
                                            <th class="py-4 px-4">حالة الجلسة</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-blue-900/40 font-medium">
                                        <tr v-for="s in filteredSessions" :key="s.id" class="hover:bg-blue-900/30 transition-colors duration-150">
                                            <td class="py-3.5 px-4 font-mono font-bold text-sky-300">
                                                <div>{{ s.id }}</div>
                                                <div class="text-[10px] text-blue-300/70 font-normal">{{ s.caseNo }}</div>
                                            </td>
                                            <td class="py-3.5 px-4 font-mono text-white">
                                                <div class="font-bold text-blue-100">{{ s.date }}</div>
                                                <div class="text-[10px] text-sky-300">{{ s.time }}</div>
                                            </td>
                                            <td class="py-3.5 px-4 text-blue-100 font-medium">
                                                <div>{{ s.court }}</div>
                                                <div class="text-[10px] text-blue-300/70">{{ s.judgeRoom }}</div>
                                            </td>
                                            <td class="py-3.5 px-4 font-bold text-white max-w-xs leading-relaxed">
                                                {{ s.title }}
                                            </td>
                                            <td class="py-3.5 px-4 text-blue-200">{{ s.client }}</td>
                                            <td class="py-3.5 px-4 text-blue-100/90 max-w-xs leading-relaxed">
                                                {{ s.requirement }}
                                            </td>
                                            <td class="py-3.5 px-4">
                                                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-md text-[11px] font-bold border" :class="s.statusClass">
                                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                                    {{ s.statusBadge }}
                                                </span>
                                            </td>
                                        </tr>
                                        <tr v-if="filteredSessions.length === 0">
                                            <td colspan="7" class="py-8 text-center text-blue-300/70">لا توجد جلسات تطابق البحث في جدول الرول الحالي</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <!-- Footer Bar -->
                            <div class="px-6 py-3 border-t border-blue-800/50 bg-[#071830] flex justify-between items-center text-[11px] text-blue-200 font-mono">
                                <span>إجمالي الجلسات المعروضة: {{ filteredSessions.length }} من {{ realSessions.length }} جلسات</span>
                                <span class="text-sky-300 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-blue-400 animate-ping"></span>
                                    رول الجلسات محدث تلقائياً مع المحاكم
                                </span>
                            </div>

                        </div>
                    </div>

                </div>
            </section>
        </div>


        <!-- ========================================== -->
        <!-- BOTTOM HALF: DARK BLACK THEME (أسمر غامق)  -->
        <!-- ========================================== -->
        <div class="relative bg-[#04070e] text-slate-100 pt-8 pb-16 overflow-hidden">
            
            <!-- Glowing Dark Ambient Gradients -->
            <div class="absolute inset-0 pointer-events-none overflow-hidden z-0">
                <div class="absolute top-10 right-0 w-[500px] h-[500px] bg-slate-900/40 blur-[150px] rounded-full"></div>
                <div class="absolute bottom-10 left-0 w-[500px] h-[500px] bg-slate-950/60 blur-[150px] rounded-full"></div>
            </div>

            <!-- Stats Counter Section -->
            <section id="stats" class="relative py-16 border-b border-slate-800/70 bg-[#070b14]/70 z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div v-for="(stat, i) in displayStats" :key="i" class="text-center group p-6 rounded-2xl bg-[#0a0f1c] border border-slate-800/80 hover:border-blue-600/40 transition-all duration-300">
                            <div class="text-4xl md:text-5xl font-black text-white mb-2 group-hover:text-blue-400 transition-colors">
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
                        <span class="px-3.5 py-1.5 rounded-full bg-blue-500/10 border border-blue-500/20 text-blue-400 text-xs font-bold mb-4 inline-block">مميزات المنصة</span>
                        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight">كل ما يحتاجه مكتب المحاماة الحديث</h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6 text-right">
                        <div
                            v-for="(feature, index) in features"
                            :key="index"
                            class="p-8 rounded-3xl bg-[#090d17] border border-slate-800/90 hover:border-blue-600/40 transition-all duration-300 hover:-translate-y-1 group shadow-lg"
                        >
                            <div class="w-12 h-12 rounded-2xl bg-gradient-to-br from-blue-600 to-blue-800 flex items-center justify-center mb-6 text-white shadow-lg shadow-blue-950/40 group-hover:scale-110 transition-transform">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" :d="feature.icon"></path>
                                </svg>
                            </div>
                            <h3 class="text-xl font-bold text-white mb-3 group-hover:text-blue-400 transition-colors">{{ feature.title }}</h3>
                            <p class="text-slate-400 text-sm leading-relaxed">{{ feature.description }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Pricing Section -->
            <section id="pricing" class="relative py-24 border-t border-slate-800/80 bg-[#070b14]/50 z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="max-w-3xl mx-auto mb-16">
                        <span class="px-3.5 py-1.5 rounded-full bg-slate-800 border border-slate-700 text-slate-300 text-xs font-bold mb-4 inline-block">خطط الاشتراك</span>
                        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-8">خطط مرنة تناسب حجم مكتبك</h2>

                        <!-- Billing Toggle -->
                        <div class="inline-flex items-center gap-2 p-1.5 bg-[#0a0f1c] rounded-2xl border border-slate-800">
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
                                :class="isAnnualPricing ? 'bg-blue-600 text-white shadow' : 'text-slate-400 hover:text-white'"
                            >
                                <span>دفع سنوي</span>
                                <span class="px-2 py-0.5 text-[10px] font-black rounded-md" :class="isAnnualPricing ? 'bg-white text-blue-900' : 'bg-blue-500/20 text-blue-400'">
                                    شهرين مجاناً
                                </span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-8 max-w-5xl mx-auto text-right items-stretch">
                        <!-- Free Plan -->
                        <div class="p-8 rounded-3xl bg-[#090d17] border border-slate-800 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">المجانية</h3>
                                <p class="text-xs text-slate-400 mb-6">للتجربة والمحامين المستقلين</p>
                                <div class="mb-6 p-4 rounded-2xl bg-[#04070e] border border-slate-800 text-right">
                                    <span class="text-4xl font-black text-white">0</span>
                                    <span class="text-xs text-slate-400 mr-1">ج.م / {{ isAnnualPricing ? 'سنوياً' : 'شهرياً' }}</span>
                                </div>
                                <ul class="space-y-3 mb-8 text-xs font-semibold text-slate-300">
                                    <li class="flex items-center gap-2.5">✓ 1 مستخدم (محامي واحد)</li>
                                    <li class="flex items-center gap-2.5">✓ 10 موكلين</li>
                                    <li class="flex items-center gap-2.5">✓ 100 قضية</li>
                                    <li class="flex items-center gap-2.5 text-slate-500 line-through">إدارة المهام</li>
                                    <li class="flex items-center gap-2.5 text-slate-500 line-through">الفوترة وبوابة الموكلين</li>
                                </ul>
                            </div>
                            <Link href="/register" class="w-full text-center px-6 py-3.5 bg-slate-800 hover:bg-slate-700 text-white font-bold rounded-xl text-xs transition">
                                ابدأ مجاناً
                            </Link>
                        </div>

                        <!-- Pro Plan -->
                        <div class="p-8 rounded-3xl bg-[#0a0f1d] border-2 border-blue-600 flex flex-col justify-between shadow-2xl shadow-blue-950/40 relative">
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 px-4 py-1 bg-blue-600 text-white text-[11px] font-black rounded-full shadow">
                                الخيار الأكثر طلباً 🚀
                            </div>
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">الاحترافية</h3>
                                <p class="text-xs text-slate-400 mb-6">للمكاتب المتوسطة والمتنامية</p>
                                <div class="mb-6 p-4 rounded-2xl bg-[#04070e] border border-slate-800 text-right">
                                    <span class="text-4xl font-black text-white">{{ isAnnualPricing ? '1,560' : '156' }}</span>
                                    <span class="text-xs text-slate-400 mr-1">ج.م / {{ isAnnualPricing ? 'سنوياً' : 'شهرياً' }}</span>
                                </div>
                                <ul class="space-y-3 mb-8 text-xs font-semibold text-slate-200">
                                    <li class="flex items-center gap-2.5 text-blue-400 font-bold">✓ 10 مستخدمين</li>
                                    <li class="flex items-center gap-2.5">✓ 200 موكل</li>
                                    <li class="flex items-center gap-2.5">✓ 3,000 قضية</li>
                                    <li class="flex items-center gap-2.5">✓ إدارة المهام</li>
                                    <li class="flex items-center gap-2.5 text-slate-500 line-through">الفوترة وبوابة الموكلين</li>
                                </ul>
                            </div>
                            <Link href="/register" class="w-full text-center px-6 py-3.5 bg-blue-600 hover:bg-blue-500 text-white font-bold rounded-xl text-xs transition shadow-lg shadow-blue-950/50">
                                اشترك الآن
                            </Link>
                        </div>

                        <!-- Enterprise Plan -->
                        <div class="p-8 rounded-3xl bg-[#090d17] border border-slate-800 flex flex-col justify-between">
                            <div>
                                <h3 class="text-xl font-bold text-white mb-2">المؤسسات</h3>
                                <p class="text-xs text-slate-400 mb-6">للكيانات والمكاتب الكبرى</p>
                                <div class="mb-6 p-4 rounded-2xl bg-[#04070e] border border-slate-800 text-right">
                                    <span class="text-4xl font-black text-white">{{ isAnnualPricing ? '4,160' : '416' }}</span>
                                    <span class="text-xs text-slate-400 mr-1">ج.م / {{ isAnnualPricing ? 'سنوياً' : 'شهرياً' }}</span>
                                </div>
                                <ul class="space-y-3 mb-8 text-xs font-semibold text-slate-300">
                                    <li class="flex items-center gap-2.5 text-blue-400 font-bold">✓ 50 مستخدم</li>
                                    <li class="flex items-center gap-2.5">✓ 1,000 موكل</li>
                                    <li class="flex items-center gap-2.5">✓ 20,000 قضية</li>
                                    <li class="flex items-center gap-2.5">✓ إدارة المهام والفوترة والمستندات</li>
                                    <li class="flex items-center gap-2.5 text-blue-400 font-bold">✓ بوابة الموكلين</li>
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
            <footer class="border-t border-slate-800/80 py-12 bg-[#020408] text-slate-400 text-sm">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-col md:flex-row justify-between items-center gap-6">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg bg-blue-600 flex items-center justify-center text-white font-bold">
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

    </div>
</template>

