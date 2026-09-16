<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    upcomingSessions: Array,
    recentCases: Array,
    pendingTasks: Array,
});
</script>

<template>
    <Head title="لوحة التحكم الرئيسية" />

    <TenantLayout>
        <template #title>
            <div class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 font-medium">الرئيسية</Link>
                <svg class="w-4 h-4 text-stone-400 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-stone-800 dark:text-white font-bold">لوحة التحكم</span>
            </div>
        </template>

        <!-- Metrics Overview Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total Clients -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-400 flex items-center justify-center font-bold text-lg">
                        👥
                    </div>
                    <span class="text-[10px] font-bold text-stone-400 dark:text-slate-500 uppercase tracking-widest">الموكلين</span>
                </div>
                <p class="text-xs font-bold text-stone-500 dark:text-slate-400">إجمالي الموكلين</p>
                <h3 class="text-2xl font-black text-stone-800 dark:text-white mt-0.5">{{ stats.total_clients }}</h3>
            </div>

            <!-- Active Cases -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-indigo-50 dark:bg-indigo-950/60 text-indigo-700 dark:text-indigo-400 flex items-center justify-center font-bold text-lg">
                        ⚖️
                    </div>
                    <span class="text-[10px] font-bold text-stone-400 dark:text-slate-500 uppercase tracking-widest">القضايا</span>
                </div>
                <p class="text-xs font-bold text-stone-500 dark:text-slate-400">القضايا الجارية</p>
                <h3 class="text-2xl font-black text-stone-800 dark:text-white mt-0.5">{{ stats.active_cases }}</h3>
            </div>

            <!-- Pending Tasks -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-400 flex items-center justify-center font-bold text-lg">
                        📋
                    </div>
                    <span class="text-[10px] font-bold text-stone-400 dark:text-slate-500 uppercase tracking-widest">المهام</span>
                </div>
                <p class="text-xs font-bold text-stone-500 dark:text-slate-400">المهام قيد التنفيذ</p>
                <h3 class="text-2xl font-black text-amber-700 dark:text-amber-400 mt-0.5">{{ stats.pending_tasks }}</h3>
            </div>

            <!-- Upcoming Sessions -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-5 shadow-sm">
                <div class="flex items-center justify-between mb-3">
                    <div class="w-10 h-10 rounded-xl bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-400 flex items-center justify-center font-bold text-lg">
                        📅
                    </div>
                    <span class="text-[10px] font-bold text-stone-400 dark:text-slate-500 uppercase tracking-widest">الجلسات</span>
                </div>
                <p class="text-xs font-bold text-stone-500 dark:text-slate-400">الجلسات القادمة</p>
                <h3 class="text-2xl font-black text-rose-700 dark:text-rose-400 mt-0.5">{{ stats.upcoming_sessions }}</h3>
            </div>
        </div>

        <!-- Financial Summary Banner -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-6 shadow-sm mb-8">
            <div class="flex items-center justify-between border-b border-stone-100 dark:border-slate-800 pb-4 mb-4">
                <h3 class="text-base font-bold text-stone-800 dark:text-white flex items-center gap-2">
                    💳 الملخص والنمو المالي للمكتب
                </h3>
                <div class="flex gap-2">
                    <Link :href="route('invoices.index')" class="text-xs font-bold text-blue-700 dark:text-blue-400 hover:underline">الفواتير</Link>
                    <span class="text-stone-300 dark:text-slate-700">•</span>
                    <Link :href="route('payments.index')" class="text-xs font-bold text-blue-700 dark:text-blue-400 hover:underline">سندات القبض</Link>
                    <span class="text-stone-300 dark:text-slate-700">•</span>
                    <Link :href="route('expenses.index')" class="text-xs font-bold text-blue-700 dark:text-blue-400 hover:underline">المصروفات</Link>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 text-center">
                <div class="bg-stone-50 dark:bg-slate-800/60 p-4 rounded-xl border border-stone-200/60 dark:border-slate-700">
                    <p class="text-xs font-bold text-stone-500 dark:text-slate-400">إجمالي المطلوب</p>
                    <p class="text-lg font-black text-stone-800 dark:text-white mt-1">{{ Number(stats.total_invoiced).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
                <div class="bg-emerald-50/60 dark:bg-emerald-950/40 p-4 rounded-xl border border-emerald-100 dark:border-emerald-800/60">
                    <p class="text-xs font-bold text-emerald-800 dark:text-emerald-300">المقبوضات المحصلة</p>
                    <p class="text-lg font-black text-emerald-700 dark:text-emerald-400 mt-1">+ {{ Number(stats.total_collected).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
                <div class="bg-amber-50/60 dark:bg-amber-950/40 p-4 rounded-xl border border-amber-100 dark:border-amber-800/60">
                    <p class="text-xs font-bold text-amber-800 dark:text-amber-300">المتبقي غير المحصل</p>
                    <p class="text-lg font-black text-amber-700 dark:text-amber-400 mt-1">{{ Number(stats.total_unpaid).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
                <div class="bg-rose-50/60 dark:bg-rose-950/40 p-4 rounded-xl border border-rose-100 dark:border-rose-800/60">
                    <p class="text-xs font-bold text-rose-800 dark:text-rose-300">إجمالي المصروفات</p>
                    <p class="text-lg font-black text-rose-700 dark:text-rose-400 mt-1">- {{ Number(stats.total_expenses).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
                <div class="bg-blue-50 dark:bg-blue-950/60 p-4 rounded-xl border border-blue-200 dark:border-blue-800/60">
                    <p class="text-xs font-bold text-blue-800 dark:text-blue-300">صافي التدفق المالي</p>
                    <p class="text-lg font-black text-blue-700 dark:text-blue-400 mt-1">{{ Number(stats.net_revenue).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
            </div>
        </div>

        <!-- Detail Columns -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Upcoming Sessions -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-stone-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-stone-800 dark:text-white flex items-center gap-2">📅 الجلسات القادمة</h3>
                </div>
                <div v-if="upcomingSessions.length === 0" class="p-8 text-center text-stone-400 dark:text-slate-500 flex-1 flex flex-col justify-center">
                    <p class="font-bold text-stone-600 dark:text-slate-400">لا توجد جلسات قادمة</p>
                </div>
                <div v-else class="divide-y divide-stone-100 dark:divide-slate-800 flex-1">
                    <div v-for="session in upcomingSessions" :key="session.id" class="p-4 hover:bg-stone-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-stone-800 dark:text-white text-sm">{{ session.case_title }}</h4>
                                <p class="text-xs text-stone-500 dark:text-slate-400 mt-0.5">رقم القضية: {{ session.case_number }}</p>
                            </div>
                            <span class="inline-block px-2.5 py-1 rounded-lg bg-rose-50 dark:bg-rose-950/60 text-rose-700 dark:text-rose-300 text-xs font-bold border border-rose-100 dark:border-rose-800/60">
                                {{ session.session_date }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Cases -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-stone-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-stone-800 dark:text-white flex items-center gap-2">⚖️ أحدث القضايا</h3>
                    <Link :href="route('cases.index')" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700">عرض الكل</Link>
                </div>
                <div v-if="recentCases.length === 0" class="p-8 text-center text-stone-400 dark:text-slate-500 flex-1 flex flex-col justify-center">
                    <p class="font-bold text-stone-600 dark:text-slate-400">لم يتم إضافة قضايا بعد</p>
                </div>
                <div v-else class="divide-y divide-stone-100 dark:divide-slate-800 flex-1">
                    <div v-for="legalCase in recentCases" :key="legalCase.id" class="p-4 hover:bg-stone-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex justify-between items-center">
                            <div>
                                <Link :href="route('cases.show', legalCase.id)" class="font-bold text-stone-800 dark:text-white text-sm hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                                    {{ legalCase.title }}
                                </Link>
                                <p class="text-xs text-stone-500 dark:text-slate-400 mt-0.5">الموكل: {{ legalCase.client_name }}</p>
                            </div>
                            <span class="px-2.5 py-1 rounded-lg text-xs font-bold bg-blue-50 dark:bg-blue-950/60 text-blue-700 dark:text-blue-300 border border-blue-100 dark:border-blue-800/60">
                                {{ legalCase.status === 'active' ? 'جارية' : legalCase.status }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Pending Tasks -->
            <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden flex flex-col">
                <div class="p-5 border-b border-stone-100 dark:border-slate-800 flex items-center justify-between">
                    <h3 class="text-base font-bold text-stone-800 dark:text-white flex items-center gap-2">📋 مهام ملحة</h3>
                    <Link :href="route('tasks.index')" class="text-xs font-bold text-blue-600 dark:text-blue-400 hover:text-blue-700">عرض الكل</Link>
                </div>
                <div v-if="!pendingTasks || pendingTasks.length === 0" class="p-8 text-center text-stone-400 dark:text-slate-500 flex-1 flex flex-col justify-center">
                    <p class="font-bold text-stone-600 dark:text-slate-400">لا توجد مهام معلقة حالياً</p>
                </div>
                <div v-else class="divide-y divide-stone-100 dark:divide-slate-800 flex-1">
                    <div v-for="task in pendingTasks" :key="task.id" class="p-4 hover:bg-stone-50 dark:hover:bg-slate-800/50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-stone-800 dark:text-white text-sm">{{ task.title }}</h4>
                                <p v-if="task.case" class="text-xs text-blue-700 dark:text-blue-400 mt-0.5 font-bold">⚖️ {{ task.case.title }}</p>
                            </div>
                            <span class="px-2 py-0.5 rounded text-[11px] font-bold bg-amber-50 dark:bg-amber-950/60 text-amber-700 dark:text-amber-300 border border-amber-200 dark:border-amber-800/60">
                                {{ task.priority }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
