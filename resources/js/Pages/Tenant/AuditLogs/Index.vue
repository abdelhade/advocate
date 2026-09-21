<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    logs: Object,
    filters: Object,
    entityTypesMap: Object,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const actionFilter = ref(props.filters?.action || '');
const entityTypeFilter = ref(props.filters?.entity_type || '');
const sort = ref(props.filters?.sort || 'created_at');
const direction = ref(props.filters?.direction || 'desc');
const showFilters = ref(!!(props.filters?.action || props.filters?.entity_type));

const selectedLogDetails = ref(null);

let filterTimeout = null;

const filterParams = () => ({
    search: search.value || undefined,
    action: actionFilter.value || undefined,
    entity_type: entityTypeFilter.value || undefined,
    sort: sort.value !== 'created_at' ? sort.value : undefined,
    direction: direction.value !== 'desc' || sort.value !== 'created_at' ? direction.value : undefined,
});

const updateFilters = (immediate = false) => {
    clearTimeout(filterTimeout);
    const run = () => {
        router.get(route('audit-logs.index'), filterParams(), {
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

watch([search, actionFilter, entityTypeFilter], () => {
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

const hasActiveFilters = computed(() => !!(actionFilter.value || entityTypeFilter.value));

const getEntityName = (fullClass) => {
    return props.entityTypesMap[fullClass] || fullClass?.split('\\').pop() || fullClass;
};

const getActionBadgeClass = (action) => {
    switch (action) {
        case 'created':
            return 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400';
        case 'updated':
            return 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400';
        case 'deleted':
            return 'bg-red-100 text-red-700 dark:bg-red-950/50 dark:text-red-400';
        case 'restored':
            return 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400';
        default:
            return 'bg-stone-100 text-stone-700 dark:bg-slate-800 dark:text-slate-300';
    }
};

const getActionLabel = (action) => {
    switch (action) {
        case 'created':
            return 'إنشاء';
        case 'updated':
            return 'تعديل';
        case 'deleted':
            return 'حذف';
        case 'restored':
            return 'استعادة';
        default:
            return action;
    }
};

const fieldLabels = {
    name: 'الاسم',
    phone: 'رقم الهاتف',
    email: 'البريد الإلكتروني',
    national_id_or_cr: 'رقم الهوية / السجل التجاري',
    national_id: 'رقم الهوية',
    address: 'العنوان',
    notes: 'ملاحظات',
    type: 'النوع',
    case_number: 'رقم القضية',
    internal_number: 'الرقم الداخلي',
    title: 'العنوان / الموضوع',
    case_type: 'نوع القضية',
    court_name: 'المحكمة',
    circuit: 'الدائرة',
    status: 'الحالة',
    client_id: 'رقم الموكل',
    primary_lawyer_id: 'المحامي الرئيسي',
    session_date: 'تاريخ الجلسة',
    next_session_date: 'تاريخ الجلسة القادمة',
    requirements: 'المطلوبات',
    results: 'النتائج',
    description: 'الوصف',
    priority: 'الأولوية',
    due_date: 'تاريخ الاستحقاق',
    assignee_id: 'المكلّف',
    creator_id: 'المنشئ',
    completed_at: 'تاريخ الإنجاز',
    invoice_number: 'رقم الفاتورة',
    total_amount: 'المبلغ الإجمالي',
    paid_amount: 'المبلغ المدفوع',
    issue_date: 'تاريخ الإصدار',
    due_amount: 'المبلغ المستحق',
    amount: 'المبلغ',
    payment_date: 'تاريخ الدفع',
    payment_method: 'طريقة الدفع',
    reference_number: 'رقم المرجع',
    category: 'التصنيف',
    expense_date: 'تاريخ المصروف',
    file_path: 'مسار الملف',
    file_size: 'حجم الملف',
    mime_type: 'نوع الملف',
    tenant_id: 'رقم المكتب',
    created_at: 'تاريخ الإنشاء',
    updated_at: 'تاريخ التحديث',
    deleted_at: 'تاريخ الحذف',
    id: 'المعرّف',
    case_id: 'رقم القضية',
    reminder_at: 'موعد التنبيه',
};

const statusLabels = {
    active: 'جارية', closed: 'مغلقة', suspended: 'موقوفة', won: 'مكسوبة', lost: 'خاسرة',
    pending: 'قيد الانتظار', in_progress: 'قيد التنفيذ', completed: 'مكتملة', cancelled: 'ملغاة',
    paid: 'مدفوعة', unpaid: 'غير مدفوعة', partial: 'مدفوعة جزئياً',
    postponed: 'مؤجلة', judged: 'محكوم بها',
    individual: 'فرد', company: 'شركة', organization: 'مؤسسة',
    low: 'منخفضة', medium: 'متوسطة', high: 'عالية', urgent: 'عاجلة',
};

const getFieldLabel = (field) => fieldLabels[field] || field;

const formatValue = (key, value) => {
    if (value === null || value === undefined || value === '') return '—';
    if (['status', 'type', 'priority'].includes(key)) return statusLabels[value] || value;
    if (typeof value === 'boolean') return value ? 'نعم' : 'لا';
    if (typeof value === 'object') return JSON.stringify(value);
    return String(value);
};

const filterFields = (data) => {
    if (!data || typeof data !== 'object') return [];
    const hidden = ['tenant_id', 'id', 'updated_at', 'deleted_at', 'remember_token', 'password', 'email_verified_at'];
    return Object.entries(data).filter(([key]) => !hidden.includes(key));
};

const formatDateTime = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleString('ar-EG', { dateStyle: 'medium', timeStyle: 'short' });
};
</script>

<template>
    <Head title="سجل النشاطات" />

    <TenantLayout>
        <template #title>سجل النشاطات</template>

        <div class="flex items-center justify-between gap-4 mb-5">
            <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
                <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-bold text-stone-800 dark:text-white">سجل النشاطات</span>
            </nav>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-4 mb-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="بحث باسم المستخدم أو الرقم..."
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
                        v-model="actionFilter"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الإجراءات</option>
                        <option value="created">إنشاء</option>
                        <option value="updated">تعديل</option>
                        <option value="deleted">حذف</option>
                    </select>
                    <select
                        v-model="entityTypeFilter"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الكيانات</option>
                        <option v-for="(label, key) in entityTypesMap" :key="key" :value="key">{{ label }}</option>
                    </select>
                </div>
            </Transition>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div v-if="!logs.data || logs.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-if="search || hasActiveFilters">لا توجد نتائج مطابقة للبحث</p>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-else>لا توجد نشاطات مسجلة</p>
            </div>

            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-stone-50/80 dark:bg-slate-800/50 border-b border-stone-100 dark:border-slate-800">
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">المستخدم</th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('action')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الإجراء
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('action') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('action') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('entity_type')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الكيان
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('entity_type') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('entity_type') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">IP</th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('created_at')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        التاريخ
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('created_at') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('created_at') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">تفاصيل</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                            <tr
                                v-for="log in logs.data"
                                :key="log.id"
                                class="hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors duration-150"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-8 h-8 rounded-full bg-stone-100 dark:bg-slate-800 text-stone-700 dark:text-slate-300 flex items-center justify-center font-bold text-xs shrink-0 border border-stone-200 dark:border-slate-700">
                                            {{ log.user?.name ? log.user.name.charAt(0) : '—' }}
                                        </div>
                                        <div>
                                            <p class="text-xs font-bold text-stone-800 dark:text-white">{{ log.user?.name || 'النظام' }}</p>
                                            <p class="text-[11px] text-stone-400 font-mono" dir="ltr">{{ log.user?.email || '—' }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2.5 py-1 text-xs font-bold rounded-lg', getActionBadgeClass(log.action)]">
                                        {{ getActionLabel(log.action) }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2 flex-wrap">
                                        <span class="font-bold text-stone-800 dark:text-white text-xs">{{ getEntityName(log.entity_type) }}</span>
                                        <span class="text-[11px] font-mono text-stone-400 bg-stone-100 dark:bg-slate-800 px-2 py-0.5 rounded">#{{ log.entity_id }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4 text-xs font-mono text-stone-500 dark:text-slate-400" dir="ltr">{{ log.ip_address || '—' }}</td>
                                <td class="px-6 py-4 text-xs text-stone-500 dark:text-slate-400">{{ formatDateTime(log.created_at) }}</td>
                                <td class="px-6 py-4">
                                    <button
                                        v-if="log.old_values || log.new_values"
                                        type="button"
                                        @click="selectedLogDetails = log"
                                        class="px-3 py-1.5 bg-stone-100 dark:bg-slate-800 hover:bg-stone-200 dark:hover:bg-slate-700 text-stone-700 dark:text-slate-300 font-bold text-xs rounded-lg transition border border-stone-200 dark:border-slate-700"
                                    >
                                        معاينة
                                    </button>
                                    <span v-else class="text-stone-300 dark:text-slate-600 text-xs">—</span>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="logs.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100 dark:border-slate-800">
                    <p class="text-sm text-stone-500 dark:text-slate-400">
                        عرض {{ logs.from }} - {{ logs.to }} من {{ logs.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in logs.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                                :class="link.active ? 'bg-blue-700 text-white font-bold' : 'text-stone-500 dark:text-slate-400 hover:bg-stone-100 dark:hover:bg-slate-800'"
                                v-html="link.label"
                                preserve-state
                            />
                            <span v-else class="px-3 py-1.5 text-sm text-stone-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500 dark:text-slate-400 px-1">
            <span>الإجمالي <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ stats?.total ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>إنشاء <strong class="text-emerald-700 dark:text-emerald-400 font-bold tabular-nums">{{ stats?.created ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>تعديل <strong class="text-blue-700 dark:text-blue-400 font-bold tabular-nums">{{ stats?.updated ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>حذف <strong class="text-red-700 dark:text-red-400 font-bold tabular-nums">{{ stats?.deleted ?? 0 }}</strong></span>
        </div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="selectedLogDetails" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="selectedLogDetails = null">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl p-6 max-w-2xl w-full shadow-2xl max-h-[85vh] overflow-y-auto border border-stone-200 dark:border-slate-800">
                        <div class="flex items-center justify-between pb-4 border-b border-stone-100 dark:border-slate-800 mb-4">
                            <h3 class="text-base font-bold text-stone-800 dark:text-white">تفاصيل النشاط: {{ getEntityName(selectedLogDetails.entity_type) }}</h3>
                            <button type="button" @click="selectedLogDetails = null" class="text-stone-400 hover:text-stone-600 dark:hover:text-slate-300 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                            <div v-if="selectedLogDetails.new_values" class="bg-emerald-50/50 dark:bg-emerald-950/20 rounded-xl p-4 border border-emerald-200/60 dark:border-emerald-900">
                                <h4 class="text-xs font-bold text-emerald-800 dark:text-emerald-400 mb-3 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span> القيم الجديدة
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="[key, value] in filterFields(selectedLogDetails.new_values)" :key="'new-' + key" class="flex items-start gap-2 bg-white dark:bg-slate-900 rounded-lg px-3 py-2 border border-emerald-100 dark:border-emerald-900">
                                        <span class="text-[11px] font-bold text-emerald-700 dark:text-emerald-400 shrink-0 min-w-[100px]">{{ getFieldLabel(key) }}</span>
                                        <span class="text-[11px] text-stone-700 dark:text-slate-300 break-all">{{ formatValue(key, value) }}</span>
                                    </div>
                                    <p v-if="filterFields(selectedLogDetails.new_values).length === 0" class="text-xs text-stone-400 text-center py-2">لا توجد بيانات</p>
                                </div>
                            </div>
                            <div v-if="selectedLogDetails.old_values" class="bg-rose-50/50 dark:bg-rose-950/20 rounded-xl p-4 border border-rose-200/60 dark:border-rose-900">
                                <h4 class="text-xs font-bold text-rose-800 dark:text-rose-400 mb-3 flex items-center gap-1.5">
                                    <span class="w-2 h-2 rounded-full bg-rose-500"></span> القيم السابقة
                                </h4>
                                <div class="space-y-2">
                                    <div v-for="[key, value] in filterFields(selectedLogDetails.old_values)" :key="'old-' + key" class="flex items-start gap-2 bg-white dark:bg-slate-900 rounded-lg px-3 py-2 border border-rose-100 dark:border-rose-900">
                                        <span class="text-[11px] font-bold text-rose-700 dark:text-rose-400 shrink-0 min-w-[100px]">{{ getFieldLabel(key) }}</span>
                                        <span class="text-[11px] text-stone-700 dark:text-slate-300 break-all">{{ formatValue(key, value) }}</span>
                                    </div>
                                    <p v-if="filterFields(selectedLogDetails.old_values).length === 0" class="text-xs text-stone-400 text-center py-2">لا توجد بيانات</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" @click="selectedLogDetails = null" class="px-5 py-2.5 bg-stone-800 dark:bg-slate-700 text-white rounded-xl text-xs font-bold hover:bg-stone-900 dark:hover:bg-slate-600 transition">إغلاق</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
