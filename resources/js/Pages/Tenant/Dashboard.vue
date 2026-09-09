<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    stats: Object,
    upcomingSessions: Array,
    recentCases: Array,
});
</script>

<template>
    <Head title="لوحة التحكم" />

    <TenantLayout>
        <template #title>لوحة التحكم</template>

        <!-- Stats Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <!-- Total Clients -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-blue-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    </div>
                </div>
                <p class="text-sm font-semibold text-stone-500 mb-1">إجمالي الموكلين</p>
                <h3 class="text-3xl font-black text-stone-800">{{ stats.total_clients }}</h3>
            </div>

            <!-- Active Cases -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                    </div>
                </div>
                <p class="text-sm font-semibold text-stone-500 mb-1">القضايا الجارية</p>
                <h3 class="text-3xl font-black text-stone-800">{{ stats.active_cases }}</h3>
            </div>

            <!-- Upcoming Sessions -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 shadow-sm">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 rounded-xl bg-orange-50 flex items-center justify-center">
                        <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    </div>
                </div>
                <p class="text-sm font-semibold text-stone-500 mb-1">الجلسات القادمة</p>
                <h3 class="text-3xl font-black text-stone-800">{{ stats.upcoming_sessions }}</h3>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <!-- Upcoming Sessions -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-stone-800">أقرب الجلسات</h3>
                </div>
                <div v-if="upcomingSessions.length === 0" class="p-8 text-center">
                    <p class="text-stone-500">لا توجد جلسات قادمة</p>
                </div>
                <div v-else class="divide-y divide-stone-100">
                    <div v-for="session in upcomingSessions" :key="session.id" class="p-4 hover:bg-stone-50 transition-colors">
                        <div class="flex justify-between items-start">
                            <div>
                                <h4 class="font-bold text-stone-800">{{ session.case_title }}</h4>
                                <p class="text-sm text-stone-500 mt-1">رقم القضية: {{ session.case_number }}</p>
                            </div>
                            <div class="text-left">
                                <span class="inline-block px-3 py-1 rounded-lg bg-orange-100 text-orange-800 text-sm font-semibold mb-1">
                                    {{ session.session_date }}
                                </span>
                                <p class="text-xs text-stone-400" v-if="session.location">{{ session.location }}</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Cases -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                    <h3 class="text-lg font-bold text-stone-800">آخر القضايا المضافة</h3>
                    <Link :href="route('cases.index')" class="text-sm font-semibold text-blue-600 hover:text-blue-700">عرض الكل</Link>
                </div>
                <div v-if="recentCases.length === 0" class="p-8 text-center">
                    <p class="text-stone-500">لم يتم إضافة قضايا بعد</p>
                </div>
                <div v-else class="divide-y divide-stone-100">
                    <div v-for="legalCase in recentCases" :key="legalCase.id" class="p-4 hover:bg-stone-50 transition-colors">
                        <div class="flex justify-between items-center">
                            <div>
                                <Link :href="route('cases.show', legalCase.id)" class="font-bold text-stone-800 hover:text-blue-600 transition-colors">
                                    {{ legalCase.title }}
                                </Link>
                                <p class="text-sm text-stone-500 mt-1">الموكل: {{ legalCase.client_name }}</p>
                            </div>
                            <span
                                class="px-3 py-1 rounded-lg text-xs font-bold"
                                :class="{
                                    'bg-emerald-100 text-emerald-700': legalCase.status === 'active',
                                    'bg-stone-100 text-stone-600': legalCase.status === 'closed',
                                    'bg-blue-100 text-blue-700': legalCase.status === 'judged',
                                    'bg-orange-100 text-orange-700': legalCase.status === 'postponed'
                                }"
                            >
                                {{ 
                                    legalCase.status === 'active' ? 'جارية' : 
                                    (legalCase.status === 'closed' ? 'مغلقة' : 
                                    (legalCase.status === 'judged' ? 'محكوم بها' : 'مؤجلة')) 
                                }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
