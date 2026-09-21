<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    payments: Object,
    stats: Object,
    clients: Array,
    invoices: Array,
    defaultPaymentNumber: String,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const paymentMethod = ref(props.filters?.payment_method || '');
const clientId = ref(props.filters?.client_id || '');
const sort = ref(props.filters?.sort || 'created_at');
const direction = ref(props.filters?.direction || 'desc');
const showFilters = ref(!!(props.filters?.payment_method || props.filters?.client_id));

let filterTimeout = null;

const filterParams = () => ({
    search: search.value || undefined,
    payment_method: paymentMethod.value || undefined,
    client_id: clientId.value || undefined,
    sort: sort.value !== 'created_at' ? sort.value : undefined,
    direction: direction.value !== 'desc' || sort.value !== 'created_at' ? direction.value : undefined,
});

const updateFilters = (immediate = false) => {
    clearTimeout(filterTimeout);
    const run = () => {
        router.get(route('payments.index'), filterParams(), {
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

watch([search, paymentMethod, clientId], () => {
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

const showCreateModal = ref(false);

const form = useForm({
    payment_number: props.defaultPaymentNumber,
    client_id: '',
    invoice_id: '',
    amount: '',
    payment_method: 'cash',
    reference_number: '',
    payment_date: new Date().toISOString().substring(0, 10),
    notes: '',
});

const openCreateModal = () => {
    form.reset();
    form.payment_number = props.defaultPaymentNumber;
    form.payment_date = new Date().toISOString().substring(0, 10);
    showCreateModal.value = true;
};

const submitForm = () => {
    form.post(route('payments.store'), {
        onSuccess: () => showCreateModal.value = false,
    });
};

const deletePayment = (p) => {
    if (confirm(`هل أنت متأكد من إغلاق/حذف سند القبض ${p.payment_number}؟`)) {
        router.delete(route('payments.destroy', p.id));
    }
};

const methodLabels = {
    cash: 'نقداً',
    bank_transfer: 'تحويل بنكي',
    check: 'شيك مصدّق',
    card: 'بطاقة ائتمان',
};

const hasActiveFilters = computed(() => !!(paymentMethod.value || clientId.value));
</script>

<template>
    <Head title="سندات القبض" />

    <TenantLayout>
        <template #title>سندات القبض</template>

        <div class="flex items-center justify-between gap-4 mb-5">
            <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
                <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-bold text-stone-800 dark:text-white">سندات القبض</span>
            </nav>
            <button
                type="button"
                @click="openCreateModal"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-700 hover:bg-blue-800 text-white shadow-sm hover:shadow-blue-200 transition-all"
                title="تسجيل سند قبض جديد"
                aria-label="تسجيل سند قبض جديد"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-4 mb-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="بحث برقم السند أو المرجع أو الموكل..."
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
                        v-model="paymentMethod"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع طرق الدفع</option>
                        <option value="cash">نقداً</option>
                        <option value="bank_transfer">تحويل بنكي</option>
                        <option value="check">شيك</option>
                        <option value="card">بطاقة مدى/ائتمان</option>
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
            <div v-if="!payments.data || payments.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-if="search || paymentMethod || clientId">لا توجد نتائج مطابقة للبحث</p>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-else>لا توجد سندات قبض مسجلة</p>
            </div>

            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-stone-50/80 dark:bg-slate-800/50 border-b border-stone-100 dark:border-slate-800">
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('payment_number')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        رقم السند
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('payment_number') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('payment_number') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">الموكل</th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">الفاتورة المرتبطة</th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('payment_method')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        طريقة الدفع
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('payment_method') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('payment_method') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('amount')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        المبلغ
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('amount') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('amount') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('payment_date')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        التاريخ
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('payment_date') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('payment_date') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                            <tr
                                v-for="p in payments.data"
                                :key="p.id"
                                class="hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors duration-150"
                            >
                                <td class="px-6 py-4 font-mono text-sm font-bold text-blue-700 dark:text-blue-400">{{ p.payment_number }}</td>
                                <td class="px-6 py-4 text-sm font-bold text-stone-800 dark:text-white">{{ p.client?.name }}</td>
                                <td class="px-6 py-4 text-sm text-stone-500 dark:text-slate-400 font-bold">
                                    {{ p.invoice ? p.invoice.invoice_number : 'قبض مباشر' }}
                                </td>
                                <td class="px-6 py-4">
                                    <span class="px-2.5 py-1 bg-stone-100 dark:bg-slate-800 text-stone-700 dark:text-slate-200 font-bold rounded-lg text-xs border border-stone-200 dark:border-slate-700">
                                        {{ methodLabels[p.payment_method] || p.payment_method }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-sm font-bold text-emerald-700 dark:text-emerald-400 tabular-nums">+ {{ Number(p.amount).toLocaleString() }}</td>
                                <td class="px-6 py-4 text-sm text-stone-500 dark:text-slate-400">{{ new Date(p.payment_date).toLocaleDateString('ar-EG') }}</td>
                                <td class="px-6 py-4">
                                    <button
                                        @click="deletePayment(p)"
                                        class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-rose-950/30 transition-colors"
                                        title="حذف"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="payments.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100 dark:border-slate-800">
                    <p class="text-sm text-stone-500 dark:text-slate-400">
                        عرض {{ payments.from }} - {{ payments.to }} من {{ payments.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in payments.links" :key="link.label">
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
            <span>إجمالي المقبوضات <strong class="text-emerald-700 dark:text-emerald-400 font-bold tabular-nums">{{ Number(stats?.total_collected ?? 0).toLocaleString() }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>العدد <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ stats?.payments_count ?? 0 }}</strong></span>
        </div>

        <!-- Payment Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showCreateModal = false">
            <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 border border-stone-200 dark:border-slate-800 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-stone-100 dark:border-slate-800">
                    <h3 class="text-lg font-bold text-stone-800 dark:text-white">تسجيل سند قبض جديد</h3>
                    <button type="button" @click="showCreateModal = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-slate-300">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">رقم السند *</label>
                        <input v-model="form.payment_number" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-sm font-bold text-blue-700 dark:text-blue-400" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">الموكل *</label>
                        <select v-model="form.client_id" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm">
                            <option value="">-- اختر الموكل --</option>
                            <option v-for="cl in clients" :key="cl.id" :value="cl.id">{{ cl.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">الفاتورة المستهدفة (اختياري)</label>
                        <select v-model="form.invoice_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm">
                            <option value="">-- قبض مباشر (بدون فاتورة) --</option>
                            <option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                                {{ inv.invoice_number }} (إجمالي: {{ inv.total_amount }} - المتبقي: {{ inv.total_amount - inv.paid_amount }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">المبلغ المقبوض () *</label>
                            <input v-model.number="form.amount" type="number" step="0.01" min="0.01" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-sm font-black text-emerald-700 dark:text-emerald-400" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">وسيلة السداد *</label>
                            <select v-model="form.payment_method" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm">
                                <option value="cash">نقداً</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                                <option value="check">شيك</option>
                                <option value="card">بطاقة مدى/ائتمان</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">رقم المرجع / الشيك</label>
                            <input v-model="form.reference_number" type="text" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm" placeholder="اختياري" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">تاريخ القبض *</label>
                            <input v-model="form.payment_date" type="date" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-slate-800">
                        <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-stone-600 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-800">إلغاء</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm shadow-md shadow-blue-200">
                            تسجيل السند
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
