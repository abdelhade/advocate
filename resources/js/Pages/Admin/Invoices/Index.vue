<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPasswordConfirm from '@/Components/AdminPasswordConfirm.vue';
import { adminPaths } from '@/adminPaths';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    invoices: Object,
    stats: Object,
    tenants: Array,
    plans: Array,
    filters: Object,
});

const page = usePage();

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const tenantId = ref(props.filters?.tenant_id || '');

const showCreateModal = ref(false);
const createForm = ref({
    tenant_id: '',
    plan_id: '',
    plan_name: '',
    billing_period: 'yearly',
    amount: 0,
    tax_amount: 0,
    status: 'paid',
    payment_method: 'bank_transfer',
    due_date: '',
    notes: '',
});

// Live Search Tenant State
const tenantSearchQuery = ref('');
const tenantSearchResults = ref([]);
const isSearchingTenants = ref(false);
const selectedTenant = ref(null);
const showTenantDropdown = ref(false);
let searchDebounceTimer = null;

const searchTenantsLive = (query) => {
    isSearchingTenants.value = true;
    showTenantDropdown.value = true;
    clearTimeout(searchDebounceTimer);
    searchDebounceTimer = setTimeout(async () => {
        try {
            const res = await fetch(`${adminPaths.tenantsSearchApi}?q=${encodeURIComponent(query || '')}`);
            if (res.ok) {
                tenantSearchResults.value = await res.json();
            }
        } catch (e) {
            console.error('Failed to search tenants:', e);
        } finally {
            isSearchingTenants.value = false;
        }
    }, 250);
};

const selectTenantForInvoice = (tenant) => {
    selectedTenant.value = tenant;
    createForm.value.tenant_id = tenant.id;
    tenantSearchQuery.value = tenant.name;
    showTenantDropdown.value = false;
};

const clearSelectedTenant = () => {
    selectedTenant.value = null;
    createForm.value.tenant_id = '';
    tenantSearchQuery.value = '';
    searchTenantsLive('');
};

const showStatusModal = ref(false);
const statusForm = ref({
    invoice_id: null,
    invoice_number: '',
    status: 'paid',
    payment_method: 'bank_transfer',
});

const showPasswordModal = ref(false);
const passwordProcessing = ref(false);
const passwordError = ref('');
const pendingAction = ref(null);
const passwordErrorFromPage = computed(() => page.props.errors?.admin_password?.[0] || passwordError.value);

const handleSearch = () => {
    router.get(adminPaths.invoices, {
        search: search.value,
        status: status.value,
        tenant_id: tenantId.value,
    }, { preserveState: true, replace: true });
};

const resetFilters = () => {
    search.value = '';
    status.value = '';
    tenantId.value = '';
    handleSearch();
};

const onPlanSelect = () => {
    const selectedPlan = props.plans.find(p => p.id === createForm.value.plan_id);
    if (selectedPlan) {
        createForm.value.plan_name = selectedPlan.name;
        createForm.value.amount = createForm.value.billing_period === 'monthly' ? selectedPlan.price_monthly : selectedPlan.price_yearly;
    }
};

const openCreateModal = () => {
    createForm.value = {
        tenant_id: '',
        plan_id: props.plans[0]?.id || '',
        plan_name: props.plans[0]?.name || 'خطة عامة',
        billing_period: 'yearly',
        amount: props.plans[0]?.price_yearly || 0,
        tax_amount: 0,
        status: 'paid',
        payment_method: 'bank_transfer',
        due_date: new Date(Date.now() + 7 * 86400000).toISOString().split('T')[0],
        notes: '',
    };
    selectedTenant.value = null;
    tenantSearchQuery.value = '';
    searchTenantsLive('');
    showCreateModal.value = true;
};

const openStatusModal = (inv) => {
    statusForm.value = {
        invoice_id: inv.id,
        invoice_number: inv.invoice_number,
        status: inv.status,
        payment_method: inv.payment_method === 'غير محدد' ? 'bank_transfer' : inv.payment_method,
    };
    showStatusModal.value = true;
};

const requestPassword = (action) => {
    pendingAction.value = action;
    passwordError.value = '';
    showPasswordModal.value = true;
};

const runWithPassword = (adminPassword) => {
    if (!pendingAction.value) return;
    passwordProcessing.value = true;
    passwordError.value = '';

    const { url, data } = pendingAction.value;
    const payload = { ...data, admin_password: adminPassword };

    router.post(url, payload, {
        preserveScroll: true,
        onSuccess: () => {
            showPasswordModal.value = false;
            pendingAction.value = null;
            showCreateModal.value = false;
            showStatusModal.value = false;
        },
        onError: (errors) => {
            passwordError.value = errors.admin_password || 'كلمة مرور المدير غير صحيحة.';
        },
        onFinish: () => {
            passwordProcessing.value = false;
        },
    });
};

const submitCreateInvoice = () => {
    if (!createForm.value.tenant_id) {
        alert('يرجى البحث واختيار المكتب أولاً.');
        return;
    }
    requestPassword({
        url: adminPaths.invoices,
        data: createForm.value,
    });
};

const submitUpdateStatus = () => {
    requestPassword({
        url: adminPaths.invoiceStatus(statusForm.value.invoice_id),
        data: {
            status: statusForm.value.status,
            payment_method: statusForm.value.payment_method,
        },
    });
};

const formatCurrency = (val) => {
    return Number(val || 0).toLocaleString('ar-EG', { minimumFractionDigits: 2 }) + ' ج.م';
};
</script>

<template>
    <Head title="فواتير الاشتراكات الإدارية" />

    <AdminLayout>
        <template #title>فواتير اشتراكات المكاتب</template>

        <!-- Header Controls -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-black text-stone-800">إدارة فواتير اشتراكات المكاتب</h2>
                <p class="text-xs text-stone-500 mt-1">عرض وإصدار فواتير اشتراكات المكاتب في المنصة وتتبع التحصيلات المالية</p>
            </div>
            <button
                @click="openCreateModal"
                class="px-4 py-2.5 bg-red-700 hover:bg-red-800 text-white text-xs font-bold rounded-xl transition shadow-md flex items-center gap-2 self-start md:self-auto"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>إصدار فاتورة اشتراك يدوية</span>
            </button>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">إجمالي الفواتير الصادرة</p>
                    <p class="text-xl font-black text-stone-800 mt-0.5">{{ formatCurrency(stats.total_amount) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">المبالغ المحصلة (المدفوعة)</p>
                    <p class="text-xl font-black text-emerald-700 mt-0.5">{{ formatCurrency(stats.paid_amount) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">المبالغ المعلقة / المتأخرة</p>
                    <p class="text-xl font-black text-amber-700 mt-0.5">{{ formatCurrency(stats.pending_amount) }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">عدد الفواتير</p>
                    <p class="text-2xl font-black text-stone-800 mt-0.5">{{ stats.count }}</p>
                </div>
            </div>
        </div>

        <!-- Filter Bar -->
        <div class="bg-white rounded-2xl p-4 border border-stone-200/80 shadow-xs mb-6 flex flex-wrap items-center gap-3">
            <div class="flex-1 min-w-[200px]">
                <input
                    v-model="search"
                    @keyup.enter="handleSearch"
                    type="text"
                    placeholder="بحث برقم الفاتورة، اسم المكتب..."
                    class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold text-stone-800 placeholder-stone-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600 transition"
                />
            </div>

            <div class="w-40">
                <select
                    v-model="status"
                    @change="handleSearch"
                    class="w-full px-3 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600 transition"
                >
                    <option value="">جميع الحالات</option>
                    <option value="paid">🟢 مدفوعة</option>
                    <option value="pending">⏳ معلقة</option>
                    <option value="overdue">🔴 متأخرة</option>
                    <option value="cancelled">⚪ ملغاة</option>
                </select>
            </div>

            <div class="w-48">
                <select
                    v-model="tenantId"
                    @change="handleSearch"
                    class="w-full px-3 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold text-stone-800 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600 transition"
                >
                    <option value="">جميع المكاتب</option>
                    <option v-for="t in tenants" :key="t.id" :value="t.id">{{ t.name }}</option>
                </select>
            </div>

            <button
                @click="resetFilters"
                class="px-3 py-2.5 border border-stone-200 rounded-xl text-xs font-bold text-stone-500 hover:bg-stone-50 transition"
            >
                إعادة ضبط
            </button>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-xs overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-sm">
                    <thead>
                        <tr class="bg-stone-50 text-stone-500 font-semibold border-b border-stone-200/70 text-xs">
                            <th class="py-3.5 px-6">رقم الفاتورة</th>
                            <th class="py-3.5 px-6">المكتب</th>
                            <th class="py-3.5 px-6">خطة الاشتراك</th>
                            <th class="py-3.5 px-6">المبلغ الإجمالي</th>
                            <th class="py-3.5 px-6">الحالة</th>
                            <th class="py-3.5 px-6">تاريخ الإصدار</th>
                            <th class="py-3.5 px-6">تاريخ الاستحقاق</th>
                            <th class="py-3.5 px-6 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-stone-50/60 transition-colors">
                            <td class="py-4 px-6 font-mono font-bold text-red-700">
                                <Link :href="adminPaths.invoice(inv.id)" class="hover:underline">
                                    {{ inv.invoice_number }}
                                </Link>
                            </td>
                            <td class="py-4 px-6 font-bold text-stone-800">
                                <Link :href="adminPaths.tenant(inv.tenant_id)" class="hover:text-red-700 transition">
                                    {{ inv.tenant_name }}
                                </Link>
                            </td>
                            <td class="py-4 px-6">
                                <span class="font-bold text-stone-800">{{ inv.plan_name }}</span>
                                <span class="text-[11px] text-stone-400 block font-semibold">
                                    {{ inv.billing_period === 'monthly' ? 'شهري' : (inv.billing_period === 'yearly' ? 'سنوي' : 'مخصص') }}
                                </span>
                            </td>
                            <td class="py-4 px-6 font-bold text-stone-900 font-mono">
                                {{ formatCurrency(inv.total_amount) }}
                            </td>
                            <td class="py-4 px-6">
                                <span
                                    :class="[
                                        'px-2.5 py-1 rounded-full text-xs font-black border',
                                        inv.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                        inv.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                                        inv.status === 'overdue' ? 'bg-red-50 text-red-700 border-red-200' :
                                        'bg-stone-100 text-stone-600 border-stone-200'
                                    ]"
                                >
                                    {{ inv.status_label }}
                                </span>
                            </td>
                            <td class="py-4 px-6 text-xs text-stone-500 font-mono">{{ inv.issued_at }}</td>
                            <td class="py-4 px-6 text-xs text-stone-500 font-mono">{{ inv.due_date }}</td>
                            <td class="py-4 px-6">
                                <div class="flex items-center justify-center gap-2">
                                    <Link
                                        :href="adminPaths.invoice(inv.id)"
                                        class="px-3 py-1.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-lg border border-stone-200 transition flex items-center gap-1"
                                    >
                                        <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        <span>عرض وطباعة</span>
                                    </Link>
                                    <button
                                        @click="openStatusModal(inv)"
                                        class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-lg border border-amber-200 transition"
                                    >
                                        تعديل الحالة
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!invoices.data || invoices.data.length === 0">
                            <td colspan="8" class="py-12 text-center text-stone-400 text-sm">
                                لا توجد فواتير اشتراك مطابقة للبحث حالياً.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div v-if="invoices.links && invoices.links.length > 3" class="p-4 border-t border-stone-100 flex items-center justify-between">
                <div class="flex items-center gap-1">
                    <Component
                        v-for="(link, i) in invoices.links"
                        :key="i"
                        :is="link.url ? Link : 'span'"
                        :href="link.url"
                        v-html="link.label"
                        :class="[
                            'px-3 py-1.5 rounded-lg text-xs font-bold transition',
                            link.active ? 'bg-red-700 text-white' : link.url ? 'bg-stone-100 text-stone-700 hover:bg-stone-200' : 'text-stone-300'
                        ]"
                    />
                </div>
                <p class="text-xs text-stone-400 font-semibold">عرض الصفحات المتاحة</p>
            </div>
        </div>

        <!-- Modal: Create Invoice with Live Tenant Search -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showCreateModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-lg w-full shadow-2xl text-right dir-rtl overflow-visible">
                        <h3 class="text-lg font-bold text-stone-800 mb-4">إصدار فاتورة اشتراك يدوية لمكتب</h3>

                        <div class="space-y-4 mb-6">
                            <!-- Real-time Live Tenant Search Component -->
                            <div class="relative">
                                <label class="block text-xs font-bold text-stone-700 mb-1">ابحث واختر المكتب (Live Search) *</label>

                                <div v-if="selectedTenant" class="flex items-center justify-between p-3 bg-red-50/70 border border-red-200 rounded-xl text-xs font-bold text-stone-900">
                                    <div>
                                        <span class="block font-black text-red-900">{{ selectedTenant.name }}</span>
                                        <span class="text-[11px] text-stone-500 font-mono" dir="ltr">{{ selectedTenant.email }} ({{ selectedTenant.slug }})</span>
                                    </div>
                                    <button type="button" @click="clearSelectedTenant" class="text-[11px] text-red-700 hover:text-red-900 font-bold px-2.5 py-1 bg-white border border-red-200 rounded-lg shadow-xs">
                                        تغيير المكتب
                                    </button>
                                </div>

                                <div v-else class="relative">
                                    <input
                                        v-model="tenantSearchQuery"
                                        @input="searchTenantsLive(tenantSearchQuery)"
                                        @focus="showTenantDropdown = true"
                                        type="text"
                                        placeholder="اكتب اسم المكتب، البريد، أو الرابط الفرعي للبحث الفوري..."
                                        class="w-full px-4 py-2.5 bg-stone-50 border border-stone-200 rounded-xl text-xs font-bold text-stone-800 placeholder-stone-400 focus:bg-white focus:outline-none focus:ring-2 focus:ring-red-600 transition"
                                    />
                                    <div v-if="isSearchingTenants" class="absolute left-3 top-2.5 text-stone-400">
                                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                    </div>

                                    <!-- Dropdown List -->
                                    <div v-if="showTenantDropdown && tenantSearchResults.length > 0" class="absolute z-[110] right-0 left-0 mt-1 max-h-48 overflow-y-auto bg-white border border-stone-200 rounded-xl shadow-2xl divide-y divide-stone-100">
                                        <div
                                            v-for="t in tenantSearchResults"
                                            :key="t.id"
                                            @click="selectTenantForInvoice(t)"
                                            class="p-3 hover:bg-red-50/50 cursor-pointer transition text-xs font-bold text-stone-800 flex justify-between items-center"
                                        >
                                            <div>
                                                <p class="font-black text-stone-900">{{ t.name }}</p>
                                                <p class="text-[10px] text-stone-400 font-mono" dir="ltr">{{ t.email }}</p>
                                            </div>
                                            <span class="text-[10px] px-2 py-0.5 bg-stone-100 rounded-md font-mono text-stone-600">{{ t.slug }}</span>
                                        </div>
                                    </div>
                                    <div v-else-if="showTenantDropdown && !isSearchingTenants && tenantSearchQuery" class="absolute z-[110] right-0 left-0 mt-1 p-3 bg-white border border-stone-200 rounded-xl shadow-xl text-xs text-stone-400 text-center font-bold">
                                        لا توجد نتائج مطابقة لـ "{{ tenantSearchQuery }}"
                                    </div>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 mb-1">الخطة الأساسية</label>
                                    <select v-model="createForm.plan_id" @change="onPlanSelect" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold">
                                        <option v-for="p in plans" :key="p.id" :value="p.id">{{ p.name }}</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 mb-1">اسم الفعالية / الخطة *</label>
                                    <input v-model="createForm.plan_name" type="text" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold" />
                                </div>
                            </div>

                            <div class="grid grid-cols-3 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 mb-1">فترة الفوترة</label>
                                    <select v-model="createForm.billing_period" @change="onPlanSelect" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold">
                                        <option value="yearly">سنوي</option>
                                        <option value="monthly">شهري</option>
                                        <option value="custom">مخصص</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 mb-1">المبلغ (ج.م) *</label>
                                    <input v-model="createForm.amount" type="number" step="0.01" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold" />
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 mb-1">الضريبة (ج.م)</label>
                                    <input v-model="createForm.tax_amount" type="number" step="0.01" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold" />
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 mb-1">حالة الفاتورة *</label>
                                    <select v-model="createForm.status" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold">
                                        <option value="paid">🟢 مدفوعة</option>
                                        <option value="pending">⏳ معلقة</option>
                                        <option value="overdue">🔴 متأخرة</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 mb-1">طريقة الدفع</label>
                                    <select v-model="createForm.payment_method" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold">
                                        <option value="bank_transfer">تحويل بنكي</option>
                                        <option value="credit_card">بطاقة ائتمان</option>
                                        <option value="cash">نقداً</option>
                                        <option value="free">مجاني / تجريبي</option>
                                    </select>
                                </div>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-700 mb-1">ملاحظات الفاتورة</label>
                                <textarea v-model="createForm.notes" rows="2" class="w-full px-4 py-2 rounded-xl border border-stone-200 text-xs font-bold"></textarea>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button @click="showCreateModal = false" class="flex-1 py-2.5 border rounded-xl text-xs font-semibold">إلغاء</button>
                            <button @click="submitCreateInvoice" class="flex-1 py-2.5 bg-red-700 text-white rounded-xl text-xs font-bold">حفظ الفاتورة (كلمة المرور)</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Modal: Update Status -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showStatusModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showStatusModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl text-right dir-rtl">
                        <h3 class="text-base font-bold text-stone-800 mb-2">تحديث حالة الفاتورة {{ statusForm.invoice_number }}</h3>

                        <div class="space-y-4 mb-6">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 mb-1">الحالة الجديدة *</label>
                                <select v-model="statusForm.status" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold">
                                    <option value="paid">🟢 مدفوعة</option>
                                    <option value="pending">⏳ معلقة</option>
                                    <option value="overdue">🔴 متأخرة</option>
                                    <option value="cancelled">⚪ ملغاة</option>
                                </select>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-700 mb-1">طريقة وسند الدفع</label>
                                <select v-model="statusForm.payment_method" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 text-xs font-bold">
                                    <option value="bank_transfer">تحويل بنكي</option>
                                    <option value="credit_card">بطاقة ائتمان</option>
                                    <option value="cash">نقداً</option>
                                    <option value="free">مجاني / تجريبي</option>
                                </select>
                            </div>
                        </div>

                        <div class="flex gap-3">
                            <button @click="showStatusModal = false" class="flex-1 py-2.5 border rounded-xl text-xs font-semibold">إلغاء</button>
                            <button @click="submitUpdateStatus" class="flex-1 py-2.5 bg-amber-600 text-white rounded-xl text-xs font-bold">تحديث الفاتورة (كلمة المرور)</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <AdminPasswordConfirm
            :show="showPasswordModal"
            :processing="passwordProcessing"
            :error="passwordErrorFromPage"
            description="إجراء الفواتير يتطلب تأكيد كلمة مرور المدير."
            @close="showPasswordModal = false; pendingAction = null"
            @confirm="runWithPassword"
        />
    </AdminLayout>
</template>
