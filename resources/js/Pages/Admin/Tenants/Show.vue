<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPasswordConfirm from '@/Components/AdminPasswordConfirm.vue';
import { adminPaths } from '@/adminPaths';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';

const props = defineProps({
    tenant: Object,
    plans: { type: Array, default: () => [] },
});

const page = usePage();
const showExtendModal = ref(false);
const extendDays = ref(30);
const confirmDeleteModal = ref(false);
const copiedSubdomain = ref(false);
const showPlanModal = ref(false);
const selectedPlanId = ref(props.tenant?.subscription?.plan_id ?? null);
const billingPeriod = ref(props.tenant?.subscription?.billing_period === 'monthly' ? 'monthly' : 'yearly');

const showInvoiceModal = ref(false);
const invoiceForm = ref({
    tenant_id: props.tenant?.id,
    plan_id: props.tenant?.subscription?.plan_id ?? null,
    plan_name: props.tenant?.subscription?.plan_name || 'اشتراك مخصص',
    billing_period: props.tenant?.subscription?.billing_period || 'yearly',
    amount: props.tenant?.subscription?.price_yearly || 0,
    tax_amount: 0,
    status: 'paid',
    payment_method: 'bank_transfer',
    due_date: new Date().toISOString().split('T')[0],
    notes: '',
});

const openInvoiceModal = () => {
    invoiceForm.value = {
        tenant_id: props.tenant?.id,
        plan_id: props.tenant?.subscription?.plan_id ?? null,
        plan_name: props.tenant?.subscription?.plan_name || 'اشتراك مخصص',
        billing_period: props.tenant?.subscription?.billing_period || 'yearly',
        amount: props.tenant?.subscription?.billing_period === 'monthly' ? (props.tenant?.subscription?.price_monthly || 0) : (props.tenant?.subscription?.price_yearly || 0),
        tax_amount: 0,
        status: 'paid',
        payment_method: 'bank_transfer',
        due_date: new Date().toISOString().split('T')[0],
        notes: '',
    };
    showInvoiceModal.value = true;
};

const handleCreateInvoice = () => {
    requestPassword({
        method: 'post',
        url: adminPaths.invoices,
        data: invoiceForm.value,
    });
};

const showPasswordModal = ref(false);
const passwordProcessing = ref(false);
const passwordError = ref('');
const pendingAction = ref(null);
const passwordErrorFromPage = computed(() => page.props.errors?.admin_password?.[0] || passwordError.value);

const copySubdomain = () => {
    if (props.tenant?.subdomain_url) {
        navigator.clipboard.writeText(props.tenant.subdomain_url);
        copiedSubdomain.value = true;
        setTimeout(() => { copiedSubdomain.value = false; }, 2000);
    }
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

    const { method, url, data } = pendingAction.value;
    const payload = { ...(data || {}), admin_password: adminPassword };
    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showPasswordModal.value = false;
            pendingAction.value = null;
            showExtendModal.value = false;
            confirmDeleteModal.value = false;
            showPlanModal.value = false;
            showInvoiceModal.value = false;
        },
        onError: (errors) => {
            passwordError.value = errors.admin_password || 'كلمة مرور المدير غير صحيحة.';
        },
        onFinish: () => { passwordProcessing.value = false; },
    };

    if (method === 'delete') {
        router.post(url, { ...payload, _method: 'delete' }, options);
    } else {
        router.post(url, payload, options);
    }
};

const handleExtendSubscription = () => {
    requestPassword({
        method: 'post',
        url: adminPaths.tenantExtend(props.tenant.id),
        data: { days: extendDays.value },
    });
};

const toggleStatus = () => {
    requestPassword({
        method: 'post',
        url: adminPaths.tenantToggleStatus(props.tenant.id),
        data: {},
    });
};

const deleteTenant = () => {
    requestPassword({
        method: 'delete',
        url: adminPaths.tenant(props.tenant.id),
        data: {},
    });
};

const openPlanModal = () => {
    selectedPlanId.value = props.tenant?.subscription?.plan_id ?? null;
    billingPeriod.value = props.tenant?.subscription?.billing_period === 'monthly' ? 'monthly' : 'yearly';
    showPlanModal.value = true;
};

const updatePlan = () => {
    requestPassword({
        method: 'post',
        url: adminPaths.tenantPlan(props.tenant.id),
        data: {
            plan_id: selectedPlanId.value,
            billing_period: billingPeriod.value,
        },
    });
};
</script>

<template>
    <Head :title="`تفاصيل المكتب: ${tenant.name}`" />

    <AdminLayout>
        <template #title>تفاصيل المكتب والاشتراك</template>

        <div class="max-w-5xl mx-auto space-y-4 pb-6 px-2 sm:px-4 lg:px-6">
            <!-- Breadcrumbs -->
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-1.5 text-xs text-stone-400">
                    <Link :href="adminPaths.tenants" class="hover:text-red-700 transition-colors">المكاتب</Link>
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                    <span class="text-stone-800 font-bold">{{ tenant.name }}</span>
                </div>
            </div>

            <!-- Two Columns Main Layout: Right (Info & Invoices) | Left (Operations & Actions) -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 items-start">

                <!-- RIGHT COLUMN: Information & Data (lg:col-span-8) -->
                <div class="lg:col-span-8 space-y-4">

                    <!-- Metric Stat Cards -->
                    <div class="grid grid-cols-2 sm:grid-cols-5 gap-2.5">
                        <div class="bg-white rounded-xl p-3 border border-stone-200/80 shadow-2xs flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium text-stone-400 truncate">الموكلين</p>
                                <p class="text-lg font-black text-stone-800 mt-0.5 leading-none">{{ tenant.stats?.clients_count ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-3 border border-stone-200/80 shadow-2xs flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium text-stone-400 truncate">القضايا</p>
                                <p class="text-lg font-black text-stone-800 mt-0.5 leading-none">{{ tenant.stats?.cases_count ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-3 border border-stone-200/80 shadow-2xs flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium text-stone-400 truncate">المستندات</p>
                                <p class="text-lg font-black text-stone-800 mt-0.5 leading-none">{{ tenant.stats?.documents_count ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-3 border border-stone-200/80 shadow-2xs flex items-center gap-2.5">
                            <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium text-stone-400 truncate">الفواتير</p>
                                <p class="text-lg font-black text-stone-800 mt-0.5 leading-none">{{ tenant.stats?.invoices_count ?? 0 }}</p>
                            </div>
                        </div>

                        <div class="bg-white rounded-xl p-3 border border-stone-200/80 shadow-2xs flex items-center gap-2.5 col-span-2 sm:col-span-1">
                            <div class="w-8 h-8 rounded-lg bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </div>
                            <div class="min-w-0">
                                <p class="text-[10px] font-medium text-stone-400 truncate">أعضاء الفريق</p>
                                <p class="text-lg font-black text-stone-800 mt-0.5 leading-none">{{ tenant.stats?.users_count ?? 0 }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Main Office Details Card -->
                    <div class="bg-white rounded-xl border border-stone-200/80 p-4 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between pb-2.5 border-b border-stone-100">
                            <h2 class="text-xs font-bold text-stone-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1v-4a1 1 0 011-1h2a1 1 0 011 1v4h1m-4 0h4"></path></svg>
                                معلومات المكتب والاشتراك
                            </h2>
                            <span
                                :class="[
                                    'px-2 py-0.5 text-[10px] font-extrabold rounded-full border',
                                    tenant.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                    tenant.status === 'suspended' ? 'bg-red-50 text-red-700 border-red-200' :
                                    'bg-amber-50 text-amber-700 border-amber-200'
                                ]"
                            >
                                {{ tenant.status === 'active' ? '🟢 نشط' : tenant.status === 'suspended' ? '🔴 موقوف' : '⏳ قيد المراجعة' }}
                            </span>
                        </div>

                        <div class="grid grid-cols-2 sm:grid-cols-3 gap-x-3 gap-y-2.5 text-xs">
                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">اسم المكتب</p>
                                <p class="font-bold text-stone-800 truncate">{{ tenant.name }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">المعرّف (Slug)</p>
                                <p class="font-bold text-stone-800 font-mono truncate" dir="ltr">{{ tenant.slug }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">خطة الاشتراك</p>
                                <p class="font-black text-stone-900 truncate">{{ tenant.subscription?.plan_name || '—' }}</p>
                                <p class="text-[10px] text-stone-500 font-semibold">
                                    <span v-if="Number(tenant.subscription?.price_yearly || 0) === 0">مجاني</span>
                                    <span v-else>
                                        {{ tenant.subscription?.billing_period === 'monthly'
                                            ? Number(tenant.subscription.price_monthly).toLocaleString() + ' ج.م/شهر'
                                            : Number(tenant.subscription.price_yearly).toLocaleString() + ' ج.م/سنة' }}
                                    </span>
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">حالة الخطة</p>
                                <p class="font-bold text-stone-800">
                                    {{ tenant.subscription?.is_trial ? 'فترة تجريبية' : (tenant.subscription?.subscription_status || tenant.status) }}
                                </p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">تاريخ البدء</p>
                                <p class="font-bold text-stone-800 font-mono">{{ tenant.start_date }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">تاريخ الانتهاء</p>
                                <div class="flex items-center gap-1 flex-wrap">
                                    <span class="font-bold text-stone-800 font-mono">{{ tenant.end_date }}</span>
                                    <span class="text-[9px] px-1 py-0.2 rounded font-bold bg-stone-100 text-stone-600">
                                        {{ tenant.days_left }} يوم
                                    </span>
                                </div>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">البريد الإلكتروني</p>
                                <p class="font-bold text-stone-800 truncate" dir="ltr">{{ tenant.email }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">رقم الهاتف</p>
                                <p class="font-bold text-stone-800 font-mono" dir="ltr">{{ tenant.phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Subscription Invoices Section (نظام الفوترة والتحصيل) -->
                    <div class="bg-white rounded-xl border border-stone-200/80 shadow-2xs overflow-hidden">
                        <div class="px-4 py-2.5 border-b border-stone-100 flex items-center justify-between gap-2">
                            <h3 class="text-xs font-bold text-stone-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                سجل فوترة الاشتراك والتحصيل
                            </h3>
                            <button
                                @click="openInvoiceModal"
                                class="px-2.5 py-1 bg-emerald-700 hover:bg-emerald-800 text-white text-[11px] font-bold rounded-lg transition flex items-center gap-1 cursor-pointer shrink-0"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>إصدار فاتورة</span>
                            </button>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-right border-collapse text-xs min-w-[500px]">
                                <thead>
                                    <tr class="bg-stone-50/80 text-stone-500 font-semibold border-b border-stone-200/70 text-[10px]">
                                        <th class="py-2 px-3">رقم الفاتورة</th>
                                        <th class="py-2 px-3">خطة الاشتراك</th>
                                        <th class="py-2 px-3">المبلغ الإجمالي</th>
                                        <th class="py-2 px-3">الحالة</th>
                                        <th class="py-2 px-3">تاريخ الإصدار</th>
                                        <th class="py-2 px-3 text-center">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    <tr v-for="inv in tenant.subscription_invoices" :key="inv.id" class="hover:bg-stone-50/60 transition-colors">
                                        <td class="py-2 px-3 font-bold font-mono text-stone-800">{{ inv.invoice_number }}</td>
                                        <td class="py-2 px-3 font-medium text-stone-700">
                                            {{ inv.plan_name }}
                                            <span class="text-[9px] text-stone-400 font-mono">({{ inv.billing_period === 'monthly' ? 'شهري' : 'سنوي' }})</span>
                                        </td>
                                        <td class="py-2 px-3 font-black text-stone-900 font-mono">{{ inv.total_amount.toLocaleString() }} ج.م</td>
                                        <td class="py-2 px-3">
                                            <span
                                                :class="[
                                                    'px-2 py-0.5 text-[9px] font-bold rounded border',
                                                    inv.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                                                    inv.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-200' :
                                                    inv.status === 'overdue' ? 'bg-red-50 text-red-700 border-red-200' : 'bg-stone-100 text-stone-600 border-stone-200'
                                                ]"
                                            >
                                                {{ inv.status_label || (inv.status === 'paid' ? 'مدفوعة 🟢' : inv.status === 'pending' ? 'قيد الانتظار ⏳' : inv.status) }}
                                            </span>
                                        </td>
                                        <td class="py-2 px-3 text-stone-500 font-mono text-[10px]">{{ inv.issued_at }}</td>
                                        <td class="py-2 px-3 text-center">
                                            <Link :href="adminPaths.invoice(inv.id)" class="text-[11px] font-bold text-red-700 hover:underline">
                                                عرض الفاتورة
                                            </Link>
                                        </td>
                                    </tr>
                                    <tr v-if="!tenant.subscription_invoices || tenant.subscription_invoices.length === 0">
                                        <td colspan="6" class="py-5 text-center text-stone-400 text-xs">
                                            لا توجد فواتير اشتراك مسجلة لهذا المكتب حالياً.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Owner Info Card -->
                    <div class="bg-white rounded-xl border border-stone-200/80 p-4 shadow-2xs space-y-3">
                        <div class="flex items-center justify-between pb-2 border-b border-stone-100">
                            <h3 class="text-xs font-bold text-stone-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                صاحب المكتب / المالك
                            </h3>
                            <span class="px-2 py-0.5 bg-amber-50 text-amber-700 text-[10px] font-bold rounded border border-amber-200">المالك الرئيسي</span>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-3 gap-3 text-xs">
                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">الاسم الكامل</p>
                                <p class="font-bold text-stone-800">{{ tenant.owner_name }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">البريد الإلكتروني</p>
                                <p class="font-semibold text-stone-700 font-mono truncate" dir="ltr">{{ tenant.owner_email }}</p>
                            </div>

                            <div>
                                <p class="text-[10px] font-medium text-stone-400 mb-0.5">رقم التواصل</p>
                                <p class="font-semibold text-stone-700 font-mono" dir="ltr">{{ tenant.owner_phone }}</p>
                            </div>
                        </div>
                    </div>

                    <!-- Team Members Section -->
                    <div class="bg-white rounded-xl border border-stone-200/80 shadow-2xs overflow-hidden">
                        <div class="px-4 py-2.5 border-b border-stone-100 flex items-center justify-between">
                            <h3 class="text-xs font-bold text-stone-800">أعضاء الفريق والمستخدمين</h3>
                            <span class="text-[10px] font-bold bg-stone-100 text-stone-600 px-2 py-0.5 rounded-full">
                                {{ tenant.users?.length ?? 0 }} عضو
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-right border-collapse text-xs min-w-[400px]">
                                <thead>
                                    <tr class="bg-stone-50/80 text-stone-500 font-semibold border-b border-stone-200/70 text-[10px]">
                                        <th class="py-2 px-3">المستخدم</th>
                                        <th class="py-2 px-3">البريد الإلكتروني</th>
                                        <th class="py-2 px-3">رقم الهاتف</th>
                                        <th class="py-2 px-3">الدور</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-stone-100">
                                    <tr v-for="user in tenant.users" :key="user.id" class="hover:bg-stone-50/60 transition-colors">
                                        <td class="py-2 px-3 font-bold text-stone-800">
                                            <div class="flex items-center gap-2">
                                                <div class="w-6 h-6 rounded-full bg-stone-200 text-stone-700 flex items-center justify-center font-bold text-[10px] shrink-0">
                                                    {{ user.name.charAt(0) }}
                                                </div>
                                                <span>{{ user.name }}</span>
                                            </div>
                                        </td>
                                        <td class="py-2 px-3 text-stone-600 font-mono text-[10px]" dir="ltr">{{ user.email }}</td>
                                        <td class="py-2 px-3 text-stone-600 font-mono text-[10px]" dir="ltr">{{ user.phone }}</td>
                                        <td class="py-2 px-3">
                                            <span v-if="user.is_owner" class="px-1.5 py-0.5 bg-amber-50 text-amber-700 font-bold text-[9px] rounded border border-amber-200">
                                                👑 مالك
                                            </span>
                                            <span v-else class="px-1.5 py-0.5 bg-stone-100 text-stone-600 font-medium text-[9px] rounded">
                                                عضو
                                            </span>
                                        </td>
                                    </tr>
                                    <tr v-if="!tenant.users || tenant.users.length === 0">
                                        <td colspan="4" class="py-5 text-center text-stone-400 text-xs">
                                            لا يوجد مستخدمين مضافين لهذا المكتب حالياً.
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div>

                <!-- LEFT COLUMN: Operations & Action Controls (lg:col-span-4) -->
                <div class="lg:col-span-4 space-y-3 lg:sticky lg:top-20">

                    <!-- Actions Panel Card -->
                    <div class="bg-white rounded-xl border border-stone-200/80 p-4 shadow-2xs space-y-3.5">
                        <div class="pb-2 border-b border-stone-100 flex items-center justify-between">
                            <h3 class="text-xs font-bold text-stone-800 flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-stone-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"></path></svg>
                                العمليات والإجراءات
                            </h3>
                        </div>

                        <!-- Subdomain URL Box -->
                        <div class="p-2.5 rounded-lg bg-stone-50 border border-stone-200/70 space-y-1.5">
                            <p class="text-[10px] font-semibold text-stone-500">رابط المكتب الفرعي</p>
                            <a :href="tenant.subdomain_url" target="_blank" class="text-xs font-bold text-red-700 hover:underline truncate block" dir="ltr">
                                {{ tenant.subdomain_url }}
                            </a>
                            <button
                                @click="copySubdomain"
                                class="w-full py-1.5 bg-white hover:bg-stone-100 text-stone-700 text-xs font-bold rounded-md border border-stone-200 transition flex items-center justify-center gap-1 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 022 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                                <span>{{ copiedSubdomain ? 'تم النسخ!' : 'نسخ رابط المكتب' }}</span>
                            </button>
                        </div>

                        <!-- Stack of Operation Buttons -->
                        <div class="space-y-2 pt-1">
                            <a
                                :href="tenant.subdomain_url"
                                target="_blank"
                                class="w-full px-3 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 border border-stone-200"
                            >
                                <svg class="w-3.5 h-3.5 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                <span>زيارة رابط المكتب</span>
                            </a>

                            <button
                                @click="openInvoiceModal"
                                class="w-full px-3 py-2 bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 shadow-2xs cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                                <span>إصدار فاتورة جديدة</span>
                            </button>

                            <button
                                @click="showExtendModal = true"
                                class="w-full px-3 py-2 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 shadow-2xs cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>تمديد الاشتراك</span>
                            </button>

                            <button
                                @click="openPlanModal"
                                class="w-full px-3 py-2 bg-stone-900 hover:bg-stone-800 text-white text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"></path></svg>
                                <span>تغيير خطة الاشتراك</span>
                            </button>

                            <button
                                @click="toggleStatus"
                                :class="[
                                    'w-full px-3 py-2 text-xs font-bold rounded-lg transition flex items-center justify-center gap-2 cursor-pointer',
                                    tenant.status === 'active' ? 'bg-amber-100 text-amber-800 hover:bg-amber-200 border border-amber-300' : 'bg-emerald-600 hover:bg-emerald-700 text-white'
                                ]"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span>{{ tenant.status === 'active' ? 'إيقاف الاشتراك' : 'تفعيل الاشتراك' }}</span>
                            </button>

                            <button
                                @click="confirmDeleteModal = true"
                                class="w-full px-3 py-2 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-lg transition border border-red-200 flex items-center justify-center gap-2 cursor-pointer"
                            >
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                <span>حذف المكتب</span>
                            </button>
                        </div>
                    </div>

                </div>

            </div>
        </div>

        <!-- Create Subscription Invoice Modal -->
        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showInvoiceModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showInvoiceModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl text-right dir-rtl space-y-4">
                        <div class="flex items-center justify-between pb-3 border-b border-stone-100">
                            <h3 class="text-base font-bold text-stone-800 flex items-center gap-2">
                                <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                إصدار فاتورة اشتراك جديدة
                            </h3>
                            <button @click="showInvoiceModal = false" class="text-stone-400 hover:text-stone-600">✕</button>
                        </div>

                        <div class="space-y-3 text-xs">
                            <div>
                                <label class="block font-semibold text-stone-700 mb-1">اسم الخطة / وصف الفاتورة</label>
                                <input v-model="invoiceForm.plan_name" type="text" class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold text-stone-800 focus:ring-2 focus:ring-red-600/20" />
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-stone-700 mb-1">فترة الفوترة</label>
                                    <select v-model="invoiceForm.billing_period" class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold text-stone-800">
                                        <option value="yearly">سنوي</option>
                                        <option value="monthly">شهري</option>
                                        <option value="custom">مخصص</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block font-semibold text-stone-700 mb-1">حالة الفاتورة</label>
                                    <select v-model="invoiceForm.status" class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold text-stone-800">
                                        <option value="paid">مدفوعة 🟢</option>
                                        <option value="pending">قيد الانتظار ⏳</option>
                                        <option value="overdue">متأخرة 🔴</option>
                                        <option value="cancelled">ملغاة ⚪</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-3">
                                <div>
                                    <label class="block font-semibold text-stone-700 mb-1">المبلغ (ج.م)</label>
                                    <input v-model.number="invoiceForm.amount" type="number" step="0.01" class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold font-mono text-stone-800" />
                                </div>
                                <div>
                                    <label class="block font-semibold text-stone-700 mb-1">الضريبة (ج.م)</label>
                                    <input v-model.number="invoiceForm.tax_amount" type="number" step="0.01" class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold font-mono text-stone-800" />
                                </div>
                            </div>

                            <div>
                                <label class="block font-semibold text-stone-700 mb-1">طريقة الدفع</label>
                                <select v-model="invoiceForm.payment_method" class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold text-stone-800">
                                    <option value="bank_transfer">تحويل بنكي</option>
                                    <option value="cash">نقداً</option>
                                    <option value="credit_card">بطاقة ائتمان</option>
                                    <option value="vodafone_cash">فودافون كاش</option>
                                    <option value="instapay">انستا باي (InstaPay)</option>
                                </select>
                            </div>

                            <div>
                                <label class="block font-semibold text-stone-700 mb-1">تاريخ الاستحقاق</label>
                                <input v-model="invoiceForm.due_date" type="date" class="w-full px-3 py-2 rounded-xl border border-stone-200 font-bold text-stone-800" />
                            </div>

                            <div>
                                <label class="block font-semibold text-stone-700 mb-1">ملاحظات (اختياري)</label>
                                <textarea v-model="invoiceForm.notes" rows="2" class="w-full px-3 py-2 rounded-xl border border-stone-200 text-stone-800" placeholder="أي تفاصيل إضافية عن الفاتورة..."></textarea>
                            </div>
                        </div>

                        <div class="flex gap-3 pt-2">
                            <button @click="showInvoiceModal = false" class="flex-1 py-2.5 border rounded-xl text-xs font-semibold text-stone-600 hover:bg-stone-50">إلغاء</button>
                            <button @click="handleCreateInvoice" class="flex-1 py-2.5 bg-emerald-700 hover:bg-emerald-800 text-white rounded-xl text-xs font-bold shadow-sm">إصدار (يتطلب كلمة المرور)</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Extend Subscription Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showExtendModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showExtendModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl">
                        <h3 class="text-lg font-bold text-stone-800 mb-2">تمديد اشتراك المكتب</h3>
                        <p class="text-xs text-stone-500 mb-6">حدد مدة التمديد التي تريد إضافتها لاشتراك مكتب <strong class="text-stone-800">{{ tenant.name }}</strong></p>

                        <div class="space-y-3 mb-6">
                            <label
                                :class="[
                                    'flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition',
                                    extendDays === 30 ? 'border-amber-500 bg-amber-50/50' : 'border-stone-200 hover:bg-stone-50'
                                ]"
                                @click="extendDays = 30"
                            >
                                <div class="flex items-center gap-3">
                                    <input type="radio" v-model="extendDays" :value="30" class="text-amber-600 focus:ring-amber-500" />
                                    <span class="text-sm font-bold text-stone-800">شهر واحد (30 يوماً)</span>
                                </div>
                                <span class="text-xs text-amber-700 font-semibold">+30 day</span>
                            </label>

                            <label
                                :class="[
                                    'flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition',
                                    extendDays === 90 ? 'border-amber-500 bg-amber-50/50' : 'border-stone-200 hover:bg-stone-50'
                                ]"
                                @click="extendDays = 90"
                            >
                                <div class="flex items-center gap-3">
                                    <input type="radio" v-model="extendDays" :value="90" class="text-amber-600 focus:ring-amber-500" />
                                    <span class="text-sm font-bold text-stone-800">3 أشهر (90 يوماً)</span>
                                </div>
                                <span class="text-xs text-amber-700 font-semibold">+90 days</span>
                            </label>

                            <label
                                :class="[
                                    'flex items-center justify-between p-3.5 rounded-xl border cursor-pointer transition',
                                    extendDays === 365 ? 'border-amber-500 bg-amber-50/50' : 'border-stone-200 hover:bg-stone-50'
                                ]"
                                @click="extendDays = 365"
                            >
                                <div class="flex items-center gap-3">
                                    <input type="radio" v-model="extendDays" :value="365" class="text-amber-600 focus:ring-amber-500" />
                                    <span class="text-sm font-bold text-stone-800">سنة كاملة (365 يوماً)</span>
                                </div>
                                <span class="text-xs text-amber-700 font-semibold">+1 year</span>
                            </label>
                        </div>

                        <div class="flex items-center gap-3">
                            <button @click="showExtendModal = false" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition">
                                إلغاء
                            </button>
                            <button @click="handleExtendSubscription" class="flex-1 px-4 py-3 bg-amber-600 hover:bg-amber-700 text-white rounded-xl text-sm font-bold transition shadow-sm">
                                تأكيد التمديد
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Delete Confirmation Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="confirmDeleteModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="confirmDeleteModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف المكتب</h3>
                        <p class="text-xs text-stone-500 text-center mb-6">هل أنت متأكد من حذف مكتب <strong class="text-stone-800">{{ tenant.name }}</strong>؟ سيتم إلغاء الاشتراك وحذف بيانات المكتب نهائياً.</p>
                        <div class="flex items-center gap-3">
                            <button @click="confirmDeleteModal = false" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition">
                                إلغاء
                            </button>
                            <button @click="deleteTenant" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition">
                                نعم، احذف
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <Teleport to="body">
            <Transition enter-active-class="transition-all duration-200" enter-from-class="opacity-0" enter-to-class="opacity-100" leave-active-class="transition-all duration-200" leave-from-class="opacity-100" leave-to-class="opacity-0">
                <div v-if="showPlanModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showPlanModal = false">
                    <div class="bg-white rounded-2xl p-6 max-w-md w-full shadow-2xl text-right dir-rtl">
                        <h3 class="text-lg font-bold text-stone-800 mb-4">تغيير خطة الاشتراك</h3>
                        <div class="space-y-2 mb-4">
                            <label v-for="plan in plans" :key="plan.id" class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer" :class="Number(selectedPlanId) === Number(plan.id) ? 'border-red-600 bg-red-50/40' : 'border-stone-200'">
                                <input v-model="selectedPlanId" type="radio" :value="plan.id" class="mt-1" />
                                <div>
                                    <p class="text-sm font-black">{{ plan.name }}</p>
                                    <p class="text-[11px] text-stone-500">{{ plan.tagline }}</p>
                                    <p class="text-xs font-bold text-red-700 mt-1">
                                        {{ Number(plan.price_yearly) === 0 ? 'مجاني' : (Number(plan.price_monthly).toLocaleString() + ' ج.م/شهر — ' + Number(plan.price_yearly).toLocaleString() + ' ج.م/سنة') }}
                                    </p>
                                </div>
                            </label>
                        </div>
                        <select v-model="billingPeriod" class="w-full mb-4 px-4 py-3 rounded-xl border border-stone-200 text-sm font-bold">
                            <option value="yearly">سنوي</option>
                            <option value="monthly">شهري</option>
                        </select>
                        <div class="flex gap-3">
                            <button @click="showPlanModal = false" class="flex-1 py-3 border rounded-xl text-sm font-semibold">إلغاء</button>
                            <button @click="updatePlan" class="flex-1 py-3 bg-red-700 text-white rounded-xl text-sm font-bold">متابعة (يتطلب كلمة المرور)</button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <AdminPasswordConfirm
            :show="showPasswordModal"
            :processing="passwordProcessing"
            :error="passwordErrorFromPage"
            description="أي تعديل على هذا المكتب يتطلب كلمة مرور المدير."
            @close="showPasswordModal = false; pendingAction = null"
            @confirm="runWithPassword"
        />
    </AdminLayout>
</template>
