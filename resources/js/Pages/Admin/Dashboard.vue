<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { adminPaths } from '@/adminPaths';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    stats: {
        type: Object,
        default: () => ({}),
    },
    recentTenants: {
        type: Array,
        default: () => [],
    },
    chartData: {
        type: Array,
        default: () => [],
    },
    recentAuditLogs: {
        type: Array,
        default: () => [],
    },
});

const maxChartValue = (data) => {
    if (!data || data.length === 0) return 1;
    const max = Math.max(...data.map(d => d.count), 1);
    return max;
};

const formatCurrency = (val) => {
    return new Intl.NumberFormat('ar-EG', { style: 'currency', currency: 'EGP', maximumFractionDigits: 0 }).format(val || 0);
};
</script>

<template>
    <Head title="لوحة التحكم الرئيسية - مدير المنصة" />

    <AdminLayout>
        <template #title>لوحة التحكم الرئيسية</template>

        <!-- Welcome Banner -->
        <div class="relative overflow-hidden bg-gradient-to-r from-stone-900 via-stone-800 to-red-950 rounded-3xl p-8 mb-8 text-white shadow-xl">
            <div class="relative z-10 flex flex-col md:flex-row md:items-center justify-between gap-6">
                <div>
                    <div class="inline-flex items-center gap-2 px-3 py-1 rounded-full bg-red-500/20 border border-red-500/30 text-red-300 text-xs font-semibold mb-3">
                        <span class="w-2 h-2 rounded-full bg-red-400 animate-ping"></span>
                        منصة إدارة مكاتب المحاماة SaaS
                    </div>
                    <h1 class="text-2xl md:text-3xl font-black tracking-tight">مرحباً بك في لوحة الإدارة العليا ⚖️</h1>
                    <p class="text-stone-300 text-sm mt-2 max-w-xl">متابعة شاملة وأداء كلي لمكاتب المحاماة، المستخدمين، الأنشطة، وسجلات الأمان عبر المنصة.</p>
                </div>
                <div class="flex items-center gap-3">
                    <Link :href="adminPaths.tenantsCreate" class="px-5 py-3 bg-red-700 hover:bg-red-600 text-white font-bold rounded-2xl text-sm transition-all duration-200 shadow-lg shadow-red-900/40 hover:scale-105 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                        إضافة مكتب جديد
                    </Link>
                </div>
            </div>
            <!-- Glow background decorator -->
            <div class="absolute -left-12 -top-12 w-64 h-64 bg-red-600/20 rounded-full blur-3xl pointer-events-none"></div>
            <div class="absolute -right-12 -bottom-12 w-64 h-64 bg-amber-600/10 rounded-full blur-3xl pointer-events-none"></div>
        </div>

        <!-- Metric KPI Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
            <!-- Total Offices / Tenants -->
            <Link :href="adminPaths.tenants" class="group bg-white rounded-3xl border border-stone-200/80 p-6 hover:shadow-xl hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-green-600 bg-green-50 border border-green-200 px-2.5 py-1 rounded-full">
                        {{ stats.active_tenants }} نشط
                    </span>
                </div>
                <p class="text-3xl font-black text-stone-900 tracking-tight">{{ stats.total_tenants }}</p>
                <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mt-1">إجمالي المكاتب</p>
            </Link>

            <!-- Total Users / Lawyers -->
            <div class="bg-white rounded-3xl border border-stone-200/80 p-6 hover:shadow-xl hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                        <svg class="w-7 h-7 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-blue-600 bg-blue-50 border border-blue-200 px-2.5 py-1 rounded-full">
                        محامين وموظفين
                    </span>
                </div>
                <p class="text-3xl font-black text-stone-900 tracking-tight">{{ stats.total_users }}</p>
                <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mt-1">المستخدمين بالمنصة</p>
            </div>

            <!-- Total Cases -->
            <div class="bg-white rounded-3xl border border-stone-200/80 p-6 hover:shadow-xl hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center">
                        <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-amber-600 bg-amber-50 border border-amber-200 px-2.5 py-1 rounded-full">
                        قضايا قيد المتابعة
                    </span>
                </div>
                <p class="text-3xl font-black text-stone-900 tracking-tight">{{ stats.total_cases }}</p>
                <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mt-1">إجمالي القضايا</p>
            </div>

            <!-- Total Revenue -->
            <div class="bg-white rounded-3xl border border-stone-200/80 p-6 hover:shadow-xl hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-1">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-13 h-13 rounded-2xl bg-gradient-to-br from-emerald-50 to-emerald-100 flex items-center justify-center">
                        <svg class="w-7 h-7 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="text-xs font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-1 rounded-full">
                        إجمالي المعاملات
                    </span>
                </div>
                <p class="text-2xl font-black text-stone-900 tracking-tight truncate">{{ formatCurrency(stats.total_revenue) }}</p>
                <p class="text-xs font-bold text-stone-400 uppercase tracking-wider mt-1">حجم العمليات المالية</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8 mb-8">
            <!-- Chart & Onboarding Trends -->
            <div class="lg:col-span-2 bg-white rounded-3xl border border-stone-200/80 p-7 shadow-sm">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-black text-stone-800">معدل نمو وانضمام المكاتب</h2>
                        <p class="text-xs text-stone-400 mt-1">عدد المكاتب المسجلة حديثاً خلال آخر 6 أشهر</p>
                    </div>
                    <span class="text-xs font-bold text-stone-500 bg-stone-100 px-3 py-1 rounded-lg">آخر 6 أشهر</span>
                </div>
                <div class="flex items-end gap-4 h-56 pt-6">
                    <div
                        v-for="(item, index) in chartData"
                        :key="index"
                        class="flex-1 flex flex-col items-center gap-2 group"
                    >
                        <span class="text-xs font-black text-stone-700 opacity-80 group-hover:opacity-100 transition-opacity">{{ item.count }}</span>
                        <div class="w-full bg-stone-100 rounded-2xl overflow-hidden p-1" style="min-height: 12px;">
                            <div
                                class="bg-gradient-to-t from-red-700 via-red-600 to-red-400 rounded-xl transition-all duration-700 ease-out group-hover:scale-y-105"
                                :style="{ height: (item.count / maxChartValue(chartData) * 160 + 12) + 'px' }"
                            ></div>
                        </div>
                        <span class="text-xs font-bold text-stone-400 mt-1">{{ item.month }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Security Audit Feed -->
            <div class="bg-white rounded-3xl border border-stone-200/80 p-7 shadow-sm">
                <div class="flex items-center justify-between mb-5">
                    <div>
                        <h2 class="text-lg font-black text-stone-800">سجل الأمان الحقيقي</h2>
                        <p class="text-xs text-stone-400 mt-0.5">آخر الأنشطة الحساسة المسجلة بالنظام</p>
                    </div>
                    <span class="w-2.5 h-2.5 rounded-full bg-green-500 animate-ping"></span>
                </div>
                <div v-if="recentAuditLogs.length === 0" class="py-12 text-center">
                    <p class="text-stone-400 text-xs">لا توجد سجلات أمان حتى الآن</p>
                </div>
                <div v-else class="space-y-4 max-h-[280px] overflow-y-auto pr-1">
                    <div
                        v-for="log in recentAuditLogs"
                        :key="log.id"
                        class="flex items-start gap-3 p-3 rounded-2xl bg-stone-50/70 border border-stone-100 text-xs"
                    >
                        <div class="w-8 h-8 rounded-xl bg-red-100 text-red-700 flex items-center justify-center font-bold flex-shrink-0 mt-0.5">
                            ⚡
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between">
                                <span class="font-bold text-stone-800 truncate">{{ log.user_name }}</span>
                                <span class="text-[10px] text-stone-400">{{ log.created_at_human }}</span>
                            </div>
                            <p class="text-stone-600 mt-0.5 font-medium">
                                إجراء <span class="font-bold text-red-700">{{ log.action }}</span> على {{ log.entity_type }}
                            </p>
                            <span class="text-[10px] text-stone-400 font-mono block mt-1">{{ log.tenant_name }} ({{ log.ip_address }})</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Registered Offices Table -->
        <div class="bg-white rounded-3xl border border-stone-200/80 overflow-hidden shadow-sm">
            <div class="px-8 py-6 border-b border-stone-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div>
                    <h2 class="text-xl font-black text-stone-800">أحدث مكاتب المحاماة المسجلة</h2>
                    <p class="text-xs text-stone-400 mt-1">نظرة سريعة على المكاتب المنضمة للمنصة مؤخراً</p>
                </div>
                <Link :href="adminPaths.tenants" class="inline-flex items-center gap-2 px-5 py-2.5 bg-stone-900 hover:bg-stone-800 text-white text-xs font-bold rounded-xl transition-all">
                    عرض جميع المكاتب ←
                </Link>
            </div>

            <!-- Table -->
            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="bg-stone-50/80 border-b border-stone-100 text-xs font-bold text-stone-400 uppercase tracking-wider">
                            <th class="px-6 py-4">اسم المكتب</th>
                            <th class="px-6 py-4">المالك المسؤول</th>
                            <th class="px-6 py-4">البريد الإلكتروني</th>
                            <th class="px-6 py-4">المستخدمين</th>
                            <th class="px-6 py-4">الحالة</th>
                            <th class="px-6 py-4">تاريخ التسجيل</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-sm">
                        <tr
                            v-for="tenant in recentTenants"
                            :key="tenant.id"
                            class="hover:bg-red-50/20 transition-colors duration-150 group"
                        >
                            <td class="px-6 py-4 font-bold text-stone-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-700 flex items-center justify-center font-black">
                                        {{ tenant.name ? tenant.name.charAt(0) : 'م' }}
                                    </div>
                                    <div>
                                        <Link :href="adminPaths.tenant(tenant.id)" class="hover:text-red-700 transition-colors">
                                            {{ tenant.name }}
                                        </Link>
                                        <span class="block text-xs font-mono text-stone-400 font-normal">slug: {{ tenant.slug }}</span>
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-stone-600 font-medium">{{ tenant.owner_name }}</td>
                            <td class="px-6 py-4 text-stone-500 font-mono text-xs">{{ tenant.email }}</td>
                            <td class="px-6 py-4">
                                <span class="inline-flex items-center px-2.5 py-1 rounded-full bg-blue-50 text-blue-700 text-xs font-bold">
                                    {{ tenant.users_count }} مستخدم
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold"
                                    :class="tenant.status === 'active' ? 'bg-green-50 text-green-700 border border-green-200' : 'bg-red-50 text-red-700 border border-red-200'"
                                >
                                    <span class="w-1.5 h-1.5 rounded-full" :class="tenant.status === 'active' ? 'bg-green-500' : 'bg-red-500'"></span>
                                    {{ tenant.status === 'active' ? 'نشط' : 'معطل' }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-stone-400 text-xs">{{ tenant.created_at }}</td>
                            <td class="px-6 py-4 text-left">
                                <Link :href="adminPaths.tenant(tenant.id)" class="text-xs font-bold text-red-700 hover:text-red-800 transition-colors">
                                    التفاصيل ←
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
