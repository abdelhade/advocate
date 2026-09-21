<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    invoices: Object,
    stats: Object,
    filters: Object,
    clients: Array,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const clientId = ref(props.filters?.client_id || '');
const sort = ref(props.filters?.sort || 'created_at');
const direction = ref(props.filters?.direction || 'desc');
const showFilters = ref(!!(props.filters?.status || props.filters?.client_id));

let filterTimeout = null;

const filterParams = () => ({
    search: search.value || undefined,
    status: status.value || undefined,
    client_id: clientId.value || undefined,
    sort: sort.value !== 'created_at' ? sort.value : undefined,
    direction: direction.value !== 'desc' || sort.value !== 'created_at' ? direction.value : undefined,
});

const updateFilters = (immediate = false) => {
    clearTimeout(filterTimeout);
    const run = () => {
        router.get(route('invoices.index'), filterParams(), {
            preserveState: true,
            replace: true,
        });
    };
    if (immediate) {
        run();
    } else {
        filterTimeout = setTimeout(run, 400);
    }
};

watch([search, status, clientId], () => {
    updateFilters();
});

const toggleSort = (column) => {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = column;
        direction.value = 'asc';
    }
    updateFilters(true);
};

const sortIcon = (column) => {
    if (sort.value !== column) return 'none';
    return direction.value === 'asc' ? 'asc' : 'desc';
};

const statusLabels = {
    draft: { text: 'مسودة', class: 'bg-stone-100 text-stone-600 border-stone-200 dark:bg-slate-800 dark:text-slate-400 dark:border-slate-700' },
    posted: { text: 'بانتظار السداد', class: 'bg-amber-50 text-amber-700 border-amber-200 dark:bg-amber-950/50 dark:text-amber-400 dark:border-amber-900' },
    partially_paid: { text: 'مدفوعة جزئياً', class: 'bg-blue-50 text-blue-700 border-blue-200 dark:bg-blue-950/50 dark:text-blue-400 dark:border-blue-900' },
    paid: { text: 'مدفوعة بالكامل', class: 'bg-emerald-50 text-emerald-700 border-emerald-200 dark:bg-emerald-950/50 dark:text-emerald-400 dark:border-emerald-900' },
    voided: { text: 'ملغاة', class: 'bg-rose-50 text-rose-700 border-rose-200 dark:bg-rose-950/50 dark:text-rose-400 dark:border-rose-900' },
};

const deleteInvoice = (invoice) => {
    if (confirm(`هل أنت متأكد من حذف الفاتورة رقم ${invoice.invoice_number}؟`)) {
        router.delete(route('invoices.destroy', invoice.id));
    }
};

const hasActiveFilters = computed(() => !!(status.value || clientId.value));
</script>

<template>
    <Head title="الفواتير" />

    <TenantLayout>
        <template #title>الفواتير</template>

        <div class="flex items-center justify-between gap-4 mb-5">
            <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
                <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-bold text-stone-800 dark:text-white">الفواتير</span>
            </nav>
            <Link
                :href="route('invoices.create')"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-700 hover:bg-blue-800 text-white shadow-sm hover:shadow-blue-200 transition-all"
                title="إنشاء فاتورة جديدة"
                aria-label="إنشاء فاتورة جديدة"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </Link>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-4 mb-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="بحث برقم الفاتورة أو اسم الموكل..."
                        class="w-full pr-12 pl-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                    />
                </div>
                <button
                    type="button"
                    @click="showFilters = !showFilters"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold transition-all shrink-0"
                    :class="showFilters || hasActiveFilters
                        ? 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400'
                        : 'border-stone-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-stone-600 dark:text-slate-300 hover:bg-stone-50 dark:hover:bg-slate-700'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    فلاتر
                    <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-blue-600"></span>
                </button>
            </div>

            <Transition
                enter-active-class="transition-all duration-200 overflow-hidden"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-40"
                leave-active-class="transition-all duration-150 overflow-hidden"
                leave-from-class="opacity-100 max-h-40"
                leave-to-class="opacity-0 max-h-0"
            >
                <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-2 gap-3 mt-3 pt-3 border-t border-stone-100 dark:border-slate-800">
                    <select
                        v-model="status"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الحالات</option>
                        <option value="draft">مسودة</option>
                        <option value="posted">بانتظار السداد</option>
                        <option value="partially_paid">مدفوعة جزئياً</option>
                        <option value="paid">مدفوعة بالكامل</option>
                        <option value="voided">ملغاة</option>
                    </select>
                    <select
                        v-model="clientId"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الموكلين</option>
                        <option v-for="cl in clients" :key="cl.id" :value="cl.id">{{ cl.name }}</option>
                    </select>
                </div>
            </Transition>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div v-if="!invoices.data || invoices.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-if="search || status || clientId">لا توجد نتائج مطابقة للبحث</p>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-else>لا توجد فواتير مسجلة</p>
            </div>

            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-stone-50/80 dark:bg-slate-800/50 border-b border-stone-100 dark:border-slate-800">
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('invoice_number')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        رقم الفاتورة
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('invoice_number') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('invoice_number') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">الموكل</th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">القضية</th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('total_amount')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الإجمالي
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('total_amount') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('total_amount') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('paid_amount')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        المسدد
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('paid_amount') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('paid_amount') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('status')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الحالة
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('status') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('status') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('due_date')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        تاريخ الاستحقاق
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('due_date') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('due_date') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                            <tr
                                v-for="inv in invoices.data"
                                :key="inv.id"
                                class="hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors duration-150"
                            >
                                <td class="px-6 py-4 font-mono text-sm">
                                    <Link :href="route('invoices.show', inv.id)" class="font-bold text-blue-700 dark:text-blue-400 hover:underline">{{ inv.invoice_number }}</Link>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-stone-800 dark:text-white">{{ inv.client?.name }}</td>
                                <td class="px-6 py-4 text-sm text-stone-500 dark:text-slate-400">{{ inv.case ? `${inv.case.title} (${inv.case.case_number})` : '—' }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-stone-800 dark:text-white tabular-nums">{{ Number(inv.total_amount).toLocaleString() }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">{{ Number(inv.paid_amount).toLocaleString() }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2.5 py-1 text-xs font-bold rounded-lg border', statusLabels[inv.status]?.class]">
                                        {{ statusLabels[inv.status]?.text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm text-stone-500 dark:text-slate-400">{{ inv.due_date ? new Date(inv.due_date).toLocaleDateString('ar-EG') : '—' }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link :href="route('invoices.show', inv.id)" class="p-2 rounded-lg text-stone-400 hover:text-emerald-600 hover:bg-emerald-50 dark:hover:bg-emerald-950/30 transition-colors" title="عرض/طباعة">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </Link>
                                        <button
                                            @click="deleteInvoice(inv)"
                                            class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-rose-950/30 transition-colors"
                                            title="حذف"
                                        >
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
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
                            <span v-else class="px-3 py-1.5 text-sm text-stone-300 dark:text-slate-600" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500 dark:text-slate-400 px-1">
            <span>إجمالي الفواتير <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ Number(stats?.total_invoiced ?? 0).toLocaleString() }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>محصّل <strong class="text-emerald-700 dark:text-emerald-400 font-bold tabular-nums">{{ Number(stats?.total_paid ?? 0).toLocaleString() }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>متبقي <strong class="text-amber-700 dark:text-amber-400 font-bold tabular-nums">{{ Number(stats?.total_unpaid ?? 0).toLocaleString() }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>العدد <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ stats?.invoices_count ?? 0 }}</strong></span>
        </div>
    </TenantLayout>
</template>
