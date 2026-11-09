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
                            <a href="#pricing" class="text-emerald-400 hover:text-emerald-300 font-bold transition-colors duration-200">مجاني بالكامل 🎁</a>
                            <a href="#app-download" class="text-sky-300 hover:text-white font-bold transition-colors duration-200 flex items-center gap-1.5 bg-blue-900/40 px-3 py-1.5 rounded-xl border border-blue-700/50 text-xs">
                                <span>📱</span><span>تطبيق الهاتف</span>
                            </a>
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

                    <!-- CTA Buttons -->
                    <div
                        class="mt-8 flex flex-wrap items-center justify-center gap-4 transition-all duration-700 delay-300"
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
                                سجل مكتبك مجاناً الآن
                                <svg class="w-5 h-5 rtl:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"></path></svg>
                            </Link>
                        </template>

                        <!-- Download AAB App Button -->
                        <a
                            href="/downloads/jalsat-release.aab"
                            download="jalsat-release.aab"
                            class="px-7 py-4 bg-emerald-500/15 hover:bg-emerald-500/25 border-2 border-emerald-500/50 hover:border-emerald-400 text-emerald-300 hover:text-white font-black rounded-2xl shadow-xl shadow-emerald-950/40 transition-all duration-200 hover:-translate-y-0.5 text-base flex items-center justify-center gap-2.5"
                        >
                            <svg class="w-5 h-5 text-emerald-400" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-1.0001 0-.5516.4482-1.0002.9993-1.0002.5516 0 .9997.4486.9997 1.0002 0 .5515-.4481 1.0001-.9997 1.0001m-11.046 0c-.5511 0-.9993-.4486-.9993-1.0001 0-.5516.4482-1.0002.9993-1.0002.5516 0 .9997.4486.9997 1.0002 0 .5515-.4481 1.0001-.9997 1.0001m11.4045-6.02l1.9965-3.456a.416.416 0 00-.1522-.5676.416.416 0 00-.5676.1522l-2.0223 3.503C15.5898 8.4116 13.844 8.082 12 8.082c-1.8445 0-3.5903.3296-5.1362.871L4.8415 5.45a.416.416 0 00-.5676-.1522.416.416 0 00-.1522.5676l1.9965 3.456C2.6874 11.0428.3435 14.8587.0544 19.3333h23.8912c-.2891-4.4746-2.633-8.2905-6.0645-10.0119"/>
                            </svg>
                            <span>تحميل تطبيق الأندرويد (AAB)</span>
                        </a>
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

            <!-- Pricing Section (100% Free Forever) -->
            <section id="pricing" class="relative py-24 border-t border-slate-800/80 bg-[#070b14]/50 z-10">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <div class="max-w-3xl mx-auto mb-12">
                        <span class="px-4 py-1.5 rounded-full bg-emerald-500/10 border border-emerald-500/30 text-emerald-400 text-xs font-black mb-4 inline-flex items-center gap-2">
                            <span>🎁</span>
                            <span>المنصة مجانية 100% مدى الحياة</span>
                        </span>
                        <h2 class="text-3xl sm:text-5xl font-black text-white tracking-tight mb-4">كافة ميزات النظام مجانية بالكامل</h2>
                        <p class="text-base text-slate-300">
                            لا حاجة لبطاقة بنكية أو اشتراكات شهرية. منصة جلسات مفتوحة بالكامل لدعم المحامين ومكاتب المحاماة في مصر والوطن العربي.
                        </p>
                    </div>

                    <!-- Single Comprehensive Free Plan Card -->
                    <div class="max-w-4xl mx-auto rounded-3xl bg-gradient-to-b from-[#0e1628] to-[#090d18] border-2 border-emerald-500/40 p-8 sm:p-12 shadow-2xl shadow-emerald-950/20 text-right relative overflow-hidden">
                        <div class="absolute top-0 right-0 left-0 h-1.5 bg-gradient-to-r from-emerald-500 via-teal-400 to-emerald-600"></div>

                        <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-8 border-b border-slate-800">
                            <div>
                                <div class="inline-block px-3 py-1 bg-emerald-500/20 text-emerald-400 text-xs font-black rounded-lg mb-2">
                                    الخطة المجانية الشاملة
                                </div>
                                <h3 class="text-2xl sm:text-3xl font-black text-white">جلسات للمحاماة (مجاني مدى الحياة)</h3>
                                <p class="text-sm text-slate-400 mt-1">كافة الأدوات والمميزات متاحة بدون أي قيود على الاستخدام</p>
                            </div>

                            <div class="p-4 sm:p-6 rounded-2xl bg-[#04070e] border border-slate-800 text-center md:text-right min-w-[200px]">
                                <div class="flex items-baseline justify-center md:justify-start gap-1">
                                    <span class="text-5xl font-black text-white">0</span>
                                    <span class="text-sm font-bold text-emerald-400">ج.م</span>
                                </div>
                                <span class="text-xs text-slate-400 font-semibold block mt-1">مجاني دائماً وبلا مقابل</span>
                            </div>
                        </div>

                        <!-- Feature Grid -->
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 py-8 border-b border-slate-800 text-sm font-semibold text-slate-200">
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>عدد غير محدود من القضايا والجلسات</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>عدد غير محدود من الموكلين وبياناتهم</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>إضافة فريق العمل والمحامين المعاونين بلا حدود</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>إدارة المهام والأعمال وتوزيعها مع التنبيهات</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>إدارة الفواتير وأتعاب المحاماة وسندات القبض</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>تسجيل المصروفات القضائية والمكتبية والتقارير المالية</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>إدارة المستندات والأوراق والتوكيلات إلكترونياً</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>بوابة الموكلين التفاعلية لمتابعة قضاياهم</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>نطاق فرعي مخصص لمكتبك (office.jalsateg.com)</span>
                            </div>
                            <div class="flex items-center gap-3">
                                <span class="w-6 h-6 rounded-full bg-emerald-500/20 text-emerald-400 flex items-center justify-center text-xs shrink-0 font-bold">✓</span>
                                <span>نسخ احتياطي يومي وحماية كاملة للبيانات</span>
                            </div>
                        </div>

                        <div class="pt-8 flex flex-col sm:flex-row items-center justify-between gap-6">
                            <div class="text-xs text-slate-400 font-medium">
                                * التفعيل فوري وبدون انتظار، وبدون إدخال أي بطاقة بنكية.
                            </div>
                            <Link href="/register" class="w-full sm:w-auto px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-xl text-sm transition shadow-lg shadow-emerald-500/30 text-center">
                                سجل مكتبك الآن مجاناً بالكامل 🚀
                            </Link>
                        </div>
                    </div>
                </div>
            </section>

            <!-- Dedicated App Download Section (AAB & APK) -->
            <section id="app-download" class="relative py-20 border-t border-slate-800/80 bg-gradient-to-b from-[#080d19] to-[#04070e] z-10">
                <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="rounded-3xl bg-gradient-to-br from-[#0c1e38] via-[#09172c] to-[#050d1a] border-2 border-emerald-500/30 p-8 sm:p-12 shadow-2xl relative overflow-hidden flex flex-col lg:flex-row items-center justify-between gap-10">
                        
                        <div class="absolute -top-24 -right-24 w-72 h-72 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>

                        <div class="text-right max-w-xl">
                            <span class="px-3.5 py-1.5 rounded-full bg-emerald-500/20 border border-emerald-500/40 text-emerald-400 text-xs font-black inline-flex items-center gap-2 mb-4">
                                <span>📱</span>
                                <span>تطبيق أندرويد متكامل</span>
                            </span>
                            <h2 class="text-3xl sm:text-4xl font-black text-white tracking-tight leading-snug mb-3">
                                تطبيق منصة جلسات على هاتفك الذكي
                            </h2>
                            <p class="text-sm sm:text-base text-slate-300 leading-relaxed mb-6">
                                تابع جدول جلساتك اليومية، تفاصيل القضايا، ومستندات الموكلين مباشرة من هاتفك في أي وقت ومن أي مكان مع إمكانية رفع وتصوير المستندات والتحميل المباشر.
                            </p>

                            <div class="flex flex-wrap items-center gap-4 text-xs font-semibold text-slate-300">
                                <span class="flex items-center gap-1.5"><span class="text-emerald-400 font-bold">✓</span> يدعم النطاقات الفرعية لمكتبك</span>
                                <span class="flex items-center gap-1.5"><span class="text-emerald-400 font-bold">✓</span> رفع وتصوير الملفات</span>
                                <span class="flex items-center gap-1.5"><span class="text-emerald-400 font-bold">✓</span> تحميل مستندات PDF</span>
                            </div>
                        </div>

                        <!-- Action Buttons Container -->
                        <div class="flex flex-col sm:flex-row lg:flex-col gap-4 w-full lg:w-auto shrink-0">
                            <!-- AAB Download Button -->
                            <a
                                href="/downloads/jalsat-release.aab"
                                download="jalsat-release.aab"
                                class="px-8 py-4 bg-emerald-500 hover:bg-emerald-400 text-slate-950 font-black rounded-2xl shadow-xl shadow-emerald-950/50 transition-all duration-200 hover:-translate-y-0.5 text-center flex items-center justify-center gap-3 text-sm"
                            >
                                <svg class="w-6 h-6 text-slate-950 shrink-0" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17.523 15.3414c-.5511 0-.9993-.4486-.9993-1.0001 0-.5516.4482-1.0002.9993-1.0002.5516 0 .9997.4486.9997 1.0002 0 .5515-.4481 1.0001-.9997 1.0001m-11.046 0c-.5511 0-.9993-.4486-.9993-1.0001 0-.5516.4482-1.0002.9993-1.0002.5516 0 .9997.4486.9997 1.0002 0 .5515-.4481 1.0001-.9997 1.0001m11.4045-6.02l1.9965-3.456a.416.416 0 00-.1522-.5676.416.416 0 00-.5676.1522l-2.0223 3.503C15.5898 8.4116 13.844 8.082 12 8.082c-1.8445 0-3.5903.3296-5.1362.871L4.8415 5.45a.416.416 0 00-.5676-.1522.416.416 0 00-.1522.5676l1.9965 3.456C2.6874 11.0428.3435 14.8587.0544 19.3333h23.8912c-.2891-4.4746-2.633-8.2905-6.0645-10.0119"/>
                                </svg>
                                <div class="text-right">
                                    <div class="text-[11px] text-slate-800 font-bold leading-tight">جاهز لـ Google Play</div>
                                    <div class="text-base font-black">تحميل حزمة التطبيق (AAB)</div>
                                </div>
                            </a>

                            <!-- APK Direct Install Button -->
                            <a
                                href="/downloads/jalsat-release.apk"
                                download="jalsat-release.apk"
                                class="px-8 py-4 bg-slate-800/90 hover:bg-slate-700/90 border border-slate-700 text-white font-black rounded-2xl shadow-lg transition-all duration-200 hover:-translate-y-0.5 text-center flex items-center justify-center gap-3 text-sm"
                            >
                                <svg class="w-6 h-6 text-blue-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                                </svg>
                                <div class="text-right">
                                    <div class="text-[11px] text-slate-400 font-bold leading-tight">تثبيت مباشر على الهاتف</div>
                                    <div class="text-base font-black">تحميل ملف APK</div>
                                </div>
                            </a>
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

