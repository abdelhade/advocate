<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    tenants: {
        type: Array,
        default: () => [],
    },
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
});

const maxChartValue = (data) => {
    const max = Math.max(...data.map(d => d.count), 1);
    return max;
};
</script>

<template>
    <Head title="لوحة التحكم - المدير" />

    <AdminLayout>
        <template #title>لوحة التحكم</template>

        <!-- Stats Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <!-- Total Tenants -->
            <Link :href="route('admin.tenants.index')" class="group bg-white rounded-2xl border border-stone-200/80 p-6 hover:shadow-lg hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-red-50 to-red-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                    </div>
                    <svg class="w-5 h-5 text-stone-300 group-hover:text-red-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
                <p class="text-3xl font-black text-stone-900">{{ stats.total_tenants }}</p>
                <p class="text-sm text-stone-500 mt-1">إجمالي المكاتب</p>
            </Link>

            <!-- Total Domains -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 hover:shadow-lg hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-50 to-blue-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                    </div>
                </div>
                <p class="text-3xl font-black text-stone-900">{{ stats.total_domains }}</p>
                <p class="text-sm text-stone-500 mt-1">إجمالي النطاقات</p>
            </div>

            <!-- Total Admins -->
            <Link :href="route('admin.admins.index')" class="group bg-white rounded-2xl border border-stone-200/80 p-6 hover:shadow-lg hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-amber-50 to-amber-100 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </div>
                    <svg class="w-5 h-5 text-stone-300 group-hover:text-amber-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                </div>
                <p class="text-3xl font-black text-stone-900">{{ stats.total_admins }}</p>
                <p class="text-sm text-stone-500 mt-1">المديرين</p>
            </Link>

            <!-- System Status -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 hover:shadow-lg hover:shadow-stone-200/50 transition-all duration-300 hover:-translate-y-0.5">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-green-50 to-green-100 flex items-center justify-center">
                        <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <span class="flex items-center gap-1.5">
                        <span class="w-2 h-2 rounded-full bg-green-500 animate-pulse"></span>
                    </span>
                </div>
                <p class="text-3xl font-black text-green-600">يعمل</p>
                <p class="text-sm text-stone-500 mt-1">حالة النظام</p>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Chart -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200/80 p-6">
                <div class="flex items-center justify-between mb-6">
                    <div>
                        <h2 class="text-lg font-bold text-stone-800">التسجيلات الشهرية</h2>
                        <p class="text-sm text-stone-400 mt-0.5">المكاتب المسجلة في آخر 6 أشهر</p>
                    </div>
                </div>
                <div class="flex items-end gap-3 h-48">
                    <div
                        v-for="(item, index) in chartData"
                        :key="index"
                        class="flex-1 flex flex-col items-center gap-2"
                    >
                        <span class="text-xs font-bold text-stone-600">{{ item.count }}</span>
                        <div class="w-full bg-stone-100 rounded-xl overflow-hidden" style="min-height: 8px;">
                            <div
                                class="bg-gradient-to-t from-red-600 to-red-400 rounded-xl transition-all duration-700 ease-out"
                                :style="{ height: (item.count / maxChartValue(chartData) * 140 + 8) + 'px' }"
                            ></div>
                        </div>
                        <span class="text-[10px] text-stone-400 font-medium">{{ item.month }}</span>
                    </div>
                </div>
            </div>

            <!-- Recent Tenants -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-bold text-stone-800">آخر المكاتب</h2>
                    <Link :href="route('admin.tenants.index')" class="text-xs text-red-600 hover:text-red-700 font-semibold">
                        عرض الكل
                    </Link>
                </div>
                <div v-if="recentTenants.length === 0" class="py-8 text-center">
                    <p class="text-stone-400 text-sm">لا توجد مكاتب مسجلة</p>
                </div>
                <div v-else class="space-y-3">
                    <Link
                        v-for="tenant in recentTenants"
                        :key="tenant.id"
                        :href="route('admin.tenants.show', tenant.id)"
                        class="flex items-center gap-3 p-3 rounded-xl hover:bg-stone-50 transition-colors group"
                    >
                        <div class="w-10 h-10 rounded-lg bg-red-50 flex items-center justify-center flex-shrink-0 group-hover:bg-red-100 transition-colors">
                            <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-bold text-stone-800 truncate">{{ tenant.id }}</p>
                            <p class="text-[11px] text-stone-400">{{ tenant.created_at_human }}</p>
                        </div>
                        <span v-if="tenant.domains.length" class="text-[10px] text-red-600 bg-red-50 px-2 py-0.5 rounded-full font-medium">
                            {{ tenant.domains[0] }}
                        </span>
                    </Link>
                </div>
            </div>
        </div>

        <!-- Tenants Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 overflow-hidden mt-6">
            <div class="px-6 py-5 border-b border-stone-100 flex items-center justify-between">
                <div>
                    <h2 class="text-lg font-bold text-stone-800">جميع المكاتب</h2>
                    <p class="text-sm text-stone-400 mt-0.5">قائمة بجميع مكاتب المحاماة المشتركة في المنصة</p>
                </div>
                <Link :href="route('admin.tenants.create')" class="inline-flex items-center gap-2 px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-red-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    إضافة مكتب
                </Link>
            </div>

            <!-- Empty State -->
            <div v-if="tenants.length === 0" class="py-16 text-center">
                <div class="w-16 h-16 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <p class="text-stone-500 text-lg font-bold">لا توجد مكاتب مسجلة</p>
                <p class="text-stone-400 text-sm mt-1">ستظهر المكاتب هنا بمجرد تسجيلها في المنصة</p>
                <Link :href="route('admin.tenants.create')" class="inline-flex items-center gap-2 mt-4 px-5 py-2.5 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition-colors">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    إنشاء أول مكتب
                </Link>
            </div>

            <!-- Table -->
            <div v-else class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="bg-stone-50/80">
                            <th class="text-right px-6 py-3.5 text-xs font-bold text-stone-400 uppercase tracking-wider">#</th>
                            <th class="text-right px-6 py-3.5 text-xs font-bold text-stone-400 uppercase tracking-wider">معرّف المكتب</th>
                            <th class="text-right px-6 py-3.5 text-xs font-bold text-stone-400 uppercase tracking-wider">النطاقات</th>
                            <th class="text-right px-6 py-3.5 text-xs font-bold text-stone-400 uppercase tracking-wider">تاريخ الإنشاء</th>
                            <th class="text-right px-6 py-3.5 text-xs font-bold text-stone-400 uppercase tracking-wider"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr
                            v-for="(tenant, index) in tenants"
                            :key="tenant.id"
                            class="hover:bg-red-50/30 transition-colors duration-150 group"
                        >
                            <td class="px-6 py-4 text-sm text-stone-400 font-mono">{{ index + 1 }}</td>
                            <td class="px-6 py-4">
                                <Link :href="route('admin.tenants.show', tenant.id)" class="text-sm font-bold text-stone-800 hover:text-red-700 transition-colors">
                                    {{ tenant.id }}
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5">
                                    <span
                                        v-for="domain in tenant.domains"
                                        :key="domain"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-red-50 text-red-700 text-xs font-medium border border-red-100"
                                    >
                                        {{ domain }}
                                    </span>
                                    <span v-if="tenant.domains.length === 0" class="text-xs text-stone-400">—</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-stone-500">{{ tenant.created_at }}</td>
                            <td class="px-6 py-4">
                                <Link :href="route('admin.tenants.show', tenant.id)" class="text-xs font-semibold text-stone-400 hover:text-red-700 transition-colors">
                                    عرض ←
                                </Link>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </AdminLayout>
</template>
