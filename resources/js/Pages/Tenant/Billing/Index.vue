<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tenant: Object,
    invoices: Object,
});

const formatCurrency = (val) => {
    return Number(val || 0).toLocaleString('ar-EG', { minimumFractionDigits: 2 }) + ' ج.م';
};
</script>

<template>
    <Head title="الاشتراك والفوترة للمكتب" />

    <TenantLayout>
        <template #title>الاشتراك وفواتير الخدمة</template>

        <!-- Breadcrumb -->
        <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400 mb-5">
            <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
            <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
            <span class="font-bold text-stone-800 dark:text-white">الاشتراك</span>
        </nav>

        <!-- Current Subscription Banner & Info -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 sm:p-8 border border-stone-200/80 dark:border-slate-800 shadow-sm mb-5">
            <div class="flex flex-col md:flex-row md:items-center justify-between gap-6 pb-6 border-b border-stone-100 dark:border-slate-800">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 text-xs font-black rounded-full border border-blue-200 dark:border-blue-800">
                            {{ tenant.subscription?.plan_name || 'الخطة الفعالة' }}
                        </span>
                        <span
                            :class="[
                                'px-3 py-1 text-xs font-black rounded-full border',
                                tenant.status === 'active'
                                    ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800'
                                    : 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800',
                            ]"
                        >
                            {{ tenant.status === 'active' ? '🟢 اشتراك نشط' : '⏳ قيد التجديد' }}
                        </span>
                    </div>
                    <h2 class="text-2xl font-black text-stone-900 dark:text-white">خطة اشتراك مكتب {{ tenant.name }}</h2>
                    <p class="text-xs text-stone-400 dark:text-slate-500 mt-1 font-semibold">تاريخ الانتهاء المحدد: {{ tenant.subscription?.ends_at || '-' }}</p>
                </div>

                <div class="bg-stone-50 dark:bg-slate-800 p-4 rounded-2xl border border-stone-200/70 dark:border-slate-700 text-right min-w-[220px]">
                    <p class="text-xs font-bold text-stone-400 dark:text-slate-500">الأيام المتبقية في الاشتراك</p>
                    <div class="flex items-baseline gap-2 mt-1">
                        <span class="text-3xl font-black text-blue-700 dark:text-blue-400">{{ tenant.days_left }}</span>
                        <span class="text-xs font-bold text-stone-600 dark:text-slate-300">يوم متبقي</span>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-3 gap-6 pt-6">
                <div>
                    <p class="text-xs font-bold text-stone-400 dark:text-slate-500">دورة الفوترة الحالية</p>
                    <p class="text-sm font-black text-stone-800 dark:text-slate-100 mt-1">
                        {{ tenant.subscription?.billing_period === 'monthly' ? 'فوترة شهرية' : 'فوترة سنوية' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold text-stone-400 dark:text-slate-500">تاريخ بدء الفترة الحالية</p>
                    <p class="text-sm font-black text-stone-800 dark:text-slate-100 font-mono mt-1">
                        {{ tenant.subscription?.starts_at || '-' }}
                    </p>
                </div>
                <div>
                    <p class="text-xs font-bold text-stone-400 dark:text-slate-500">البريد الإلكتروني المعتمد للفواتير</p>
                    <p class="text-sm font-black text-stone-800 dark:text-slate-100 font-mono mt-1" dir="ltr">
                        {{ tenant.email }}
                    </p>
                </div>
            </div>
        </div>

        <!-- Invoices List -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div class="p-6 border-b border-stone-100 dark:border-slate-800 flex items-center justify-between">
                <div>
                    <h3 class="text-lg font-black text-stone-900 dark:text-white">سجل فواتير منصة جلسات</h3>
                    <p class="text-xs text-stone-400 dark:text-slate-500 mt-0.5 font-semibold">جميع الفواتير الصادرة لمكتبكم مع إمكانية المعاينة والطباعة</p>
                </div>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-sm">
                    <thead>
                        <tr class="bg-stone-50/80 dark:bg-slate-800/50 text-stone-500 dark:text-slate-500 font-semibold border-b border-stone-200/70 dark:border-slate-800 text-xs">
                            <th class="py-4 px-6">رقم الفاتورة</th>
                            <th class="py-4 px-6">الخدمة / الخطة</th>
                            <th class="py-4 px-6">فترة الفوترة</th>
                            <th class="py-4 px-6">الإجمالي</th>
                            <th class="py-4 px-6">الحالة</th>
                            <th class="py-4 px-6">تاريخ الإصدار</th>
                            <th class="py-4 px-6 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                        <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-stone-50/60 dark:hover:bg-slate-800/40 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-blue-700 dark:text-blue-400">
                                {{ inv.invoice_number }}
                            </td>
                            <td class="py-4 px-6 font-bold text-stone-800 dark:text-slate-100">
                                {{ inv.plan_name }}
                            </td>
                            <td class="py-4 px-6 text-xs text-stone-600 dark:text-slate-300 font-semibold">
                                {{ inv.billing_period }}
                            </td>
                            <td class="py-4 px-6 font-black text-stone-900 dark:text-white font-mono">
                                {{ formatCurrency(inv.total_amount) }}
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    :class="[
                                        'px-3 py-1 rounded-full text-xs font-black border',
                                        inv.status === 'paid'
                                            ? 'bg-emerald-50 dark:bg-emerald-950/40 text-emerald-700 dark:text-emerald-400 border-emerald-200 dark:border-emerald-800'
                                            : inv.status === 'pending'
                                              ? 'bg-amber-50 dark:bg-amber-950/40 text-amber-700 dark:text-amber-400 border-amber-200 dark:border-amber-800'
                                              : 'bg-red-50 dark:bg-red-950/40 text-red-700 dark:text-red-400 border-red-200 dark:border-red-800',
                                    ]"
                                >
                                    {{ inv.status_label }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-xs text-stone-500 dark:text-slate-400 font-mono">{{ inv.issued_at }}</td>
                            <td class="py-4 px-6 text-center">
                                <Link
                                    :href="route('tenant.billing.show', inv.id)"
                                    class="px-3 py-1.5 bg-stone-100 dark:bg-slate-800 hover:bg-stone-200 dark:hover:bg-slate-700 text-stone-700 dark:text-slate-200 text-xs font-bold rounded-xl border border-stone-200 dark:border-slate-700 transition inline-flex items-center gap-1.5"
                                >
                                    <svg class="w-3.5 h-3.5 text-stone-500 dark:text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    <span>عرض وطباعة</span>
                                </Link>
                            </td>
                        </tr>
                        <tr v-if="!invoices.data || invoices.data.length === 0">
                            <td colspan="7" class="py-12 text-center text-stone-400 dark:text-slate-500 text-sm">
                                لا توجد فواتير اشتراك مسجلة لمكتبكم حتى الآن.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div v-if="invoices.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100 dark:border-slate-800">
                <p class="text-sm text-stone-500 dark:text-slate-400">
                    عرض {{ invoices.from }} - {{ invoices.to }} من {{ invoices.total }}
                </p>
                <div class="flex items-center gap-1">
                    <template v-for="link in invoices.links" :key="link.label">
                        <Link
                            v-if="link.url"
                            :href="link.url"
                            class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                            :class="link.active ? 'bg-blue-700 text-white font-bold' : 'text-stone-500 dark:text-slate-400 hover:bg-stone-100 dark:hover:bg-slate-800'"
                            v-html="link.label"
                            preserve-state
                        />
                        <span
                            v-else
                            class="px-3 py-1.5 text-sm text-stone-300 dark:text-slate-600"
                            v-html="link.label"
                        />
                    </template>
                </div>
            </div>
        </div>

        <!-- Compact stats -->
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500 dark:text-slate-400 px-1">
            <span>عدد الفواتير <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ invoices.total ?? 0 }}</strong></span>
        </div>
    </TenantLayout>
</template>
