<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import AdminPasswordConfirm from '@/Components/AdminPasswordConfirm.vue';
import { adminPaths } from '@/adminPaths';
import { Head, Link, router, usePage } from '@inertiajs/vue3';
import { ref, watch, onMounted, onUnmounted, computed } from 'vue';

const props = defineProps({
    tenants: Object,
    plans: { type: Array, default: () => [] },
    filters: Object,
});

const page = usePage();
const search = ref(props.filters?.search || '');
const planFilter = ref(props.filters?.plan || '');
let searchTimeout = null;

const applyFilters = () => {
    router.get(adminPaths.tenants, {
        search: search.value || undefined,
        plan: planFilter.value || undefined,
    }, {
        preserveState: true,
        replace: true,
    });
};

watch(search, () => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(applyFilters, 400);
});

watch(planFilter, applyFilters);

const activeDropdown = ref(null);
const toggleDropdown = (id) => {
    activeDropdown.value = activeDropdown.value === id ? null : id;
};

const closeDropdowns = (e) => {
    if (!e.target.closest('.dropdown-container')) {
        activeDropdown.value = null;
    }
};

onMounted(() => window.addEventListener('click', closeDropdowns));
onUnmounted(() => window.removeEventListener('click', closeDropdowns));

const confirmDelete = ref(null);
const extendModalTenant = ref(null);
const extendDays = ref(30);
const planModalTenant = ref(null);
const selectedPlanId = ref(null);
const billingPeriod = ref('yearly');

const showPasswordModal = ref(false);
const passwordProcessing = ref(false);
const passwordError = ref('');
const pendingAction = ref(null);

const passwordErrorFromPage = computed(() => page.props.errors?.admin_password?.[0] || passwordError.value);

const openPlanModal = (tenant) => {
    planModalTenant.value = tenant;
    selectedPlanId.value = tenant.plan_id;
    billingPeriod.value = tenant.billing_period === 'monthly' ? 'monthly' : 'yearly';
    activeDropdown.value = null;
};

const requestPassword = (action) => {
    pendingAction.value = action;
    passwordError.value = '';
    showPasswordModal.value = true;
    activeDropdown.value = null;
};

const runWithPassword = (adminPassword) => {
    if (!pendingAction.value) return;
    passwordProcessing.value = true;
    passwordError.value = '';

    const { method, url, data, onSuccess } = pendingAction.value;
    const payload = { ...(data || {}), admin_password: adminPassword };

    const options = {
        preserveScroll: true,
        onSuccess: () => {
            showPasswordModal.value = false;
            pendingAction.value = null;
            confirmDelete.value = null;
            extendModalTenant.value = null;
            planModalTenant.value = null;
            onSuccess?.();
        },
        onError: (errors) => {
            passwordError.value = errors.admin_password || 'تعذر تنفيذ الإجراء. تحقق من كلمة المرور.';
        },
        onFinish: () => {
            passwordProcessing.value = false;
        },
    };

    if (method === 'delete') {
        router.post(url, { ...payload, _method: 'delete' }, options);
    } else {
        router.post(url, payload, options);
    }
};

const deleteTenant = (id) => {
    requestPassword({
        method: 'delete',
        url: adminPaths.tenant(id),
        data: {},
    });
};

const extendSubscription = (id, days) => {
    requestPassword({
        method: 'post',
        url: adminPaths.tenantExtend(id),
        data: { days },
    });
};

const toggleStatus = (id) => {
    requestPassword({
        method: 'post',
        url: adminPaths.tenantToggleStatus(id),
        data: {},
    });
};

const updatePlan = () => {
    if (!planModalTenant.value || !selectedPlanId.value) return;
    requestPassword({
        method: 'post',
        url: adminPaths.tenantPlan(planModalTenant.value.id),
        data: {
            plan_id: selectedPlanId.value,
            billing_period: billingPeriod.value,
        },
    });
};

const planBadgeClass = (slug) => {
    if (slug === 'enterprise') return 'bg-stone-900 text-white border-stone-800';
    if (slug === 'professional') return 'bg-red-50 text-red-700 border-red-200';
    if (slug === 'free') return 'bg-stone-100 text-stone-700 border-stone-200';
    return 'bg-amber-50 text-amber-800 border-amber-200';
};

const statusLabel = (tenant) => {
    if (tenant.subscription_status === 'trialing' || tenant.is_trial) return 'تجريبي';
    if (tenant.subscription_status === 'expired') return 'منتهي';
    if (tenant.subscription_status === 'cancelled' || tenant.status !== 'active') return 'موقوف';
    return 'نشط';
};
</script>

<template>
    <Head title="إدارة المكاتب والمشتركين" />

    <AdminLayout>
        <template #title>إدارة المكاتب والمشتركين</template>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-sm text-stone-400">إدارة تفاصيل المكاتب المسجلة، تواريخ الاشتراكات، والتمديد والإلغاء</p>
            </div>
            <Link :href="adminPaths.tenantsCreate" class="inline-flex items-center gap-2 px-5 py-3 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-red-200 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة مكتب جديد
            </Link>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 mb-6">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                <div class="relative sm:col-span-2">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="بحث باسم المكتب، البريد، أو معرّف المكتب..."
                        class="w-full pr-12 pl-4 py-3 rounded-xl border border-stone-200 bg-stone-50 text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-sm font-medium"
                    />
                </div>
                <select
                    v-model="planFilter"
                    class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 text-stone-800 text-sm font-bold focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500"
                >
                    <option value="">كل خطط الاشتراك</option>
                    <option v-for="plan in plans" :key="plan.id" :value="plan.slug">
                        {{ plan.name }} — {{ Number(plan.price_yearly) === 0 ? 'مجاني' : Number(plan.price_yearly).toLocaleString() + ' ج.م/سنة' }}
                    </option>
                </select>
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 overflow-visible">
            <!-- Empty State -->
            <div v-if="tenants.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <p class="text-stone-600 text-lg font-bold" v-if="search">لا توجد نتائج لـ "{{ search }}"</p>
                <p class="text-stone-600 text-lg font-bold" v-else>لا توجد مكاتب مسجلة</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-stone-50/80 border-b border-stone-200/80 text-stone-500 text-xs font-bold uppercase tracking-wider">
                            <th class="px-6 py-4">#</th>
                            <th class="px-6 py-4">المكتب والمسؤول</th>
                            <th class="px-6 py-4">نوع الاشتراك</th>
                            <th class="px-6 py-4">حالة الاشتراك</th>
                            <th class="px-6 py-4">تاريخ بداية الاشتراك</th>
                            <th class="px-6 py-4">تاريخ انتهاء الاشتراك</th>
                            <th class="px-6 py-4 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 font-medium text-sm">
                        <tr
                            v-for="(tenant, index) in tenants.data"
                            :key="tenant.id"
                            class="hover:bg-red-50/20 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 text-stone-400 font-mono">{{ (tenants.current_page - 1) * tenants.per_page + index + 1 }}</td>
                            <td class="px-6 py-4">
                                <Link :href="adminPaths.tenant(tenant.id)" class="block">
                                    <span class="font-bold text-stone-900 hover:text-red-700 transition-colors block text-base">{{ tenant.name }}</span>
                                    <span class="text-xs text-stone-500 font-mono">المالك: {{ tenant.owner_name }} ({{ tenant.email }})</span>
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    <span
                                        class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold border"
                                        :class="planBadgeClass(tenant.plan_slug)"
                                    >
                                        {{ tenant.plan_name }}
                                    </span>
                                    <p class="text-[11px] text-stone-500 font-semibold">
                                        <span v-if="Number(tenant.price_yearly) === 0">مجاني</span>
                                        <span v-else>
                                            {{ tenant.billing_period === 'monthly'
                                                ? Number(tenant.price_monthly).toLocaleString() + ' ج.م / شهر'
                                                : Number(tenant.price_yearly).toLocaleString() + ' ج.م / سنة' }}
                                        </span>
                                    </p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="tenant.status === 'active' && tenant.subscription_status !== 'expired'" class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-emerald-600"></span>
                                    {{ statusLabel(tenant) }}
                                </span>
                                <span v-else class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200">
                                    <span class="w-1.5 h-1.5 rounded-full bg-rose-600"></span>
                                    {{ statusLabel(tenant) }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-mono text-stone-700 dir-ltr text-right">
                                📅 {{ tenant.start_date }}
                            </td>
                            <td class="px-6 py-4 font-mono font-bold text-red-700 dir-ltr text-right">
                                ⏳ {{ tenant.end_date }}
                            </td>
                            <td class="px-6 py-4 text-center">
                                <!-- DROPDOWN ACTION MENU BUTTON -->
                                <div class="relative inline-block text-right dropdown-container">
                                    <button
                                        @click.stop="toggleDropdown(tenant.id)"
                                        class="inline-flex items-center gap-2 px-4 py-2 bg-stone-100 hover:bg-stone-200 text-stone-800 rounded-xl text-xs font-bold transition-all border border-stone-200 shadow-sm cursor-pointer"
                                    >
                                        <span>إجراءات</span>
                                        <svg class="w-4 h-4 transition-transform duration-200" :class="{ 'rotate-180': activeDropdown === tenant.id }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                    </button>

                                    <!-- Dropdown List Items -->
                                    <Transition
                                        enter-active-class="transition duration-100 ease-out"
                                        enter-from-class="transform scale-95 opacity-0"
                                        enter-to-class="transform scale-100 opacity-100"
                                        leave-active-class="transition duration-75 ease-in"
                                        leave-from-class="transform scale-100 opacity-100"
                                        leave-to-class="transform scale-95 opacity-0"
                                    >
                                        <div
                                            v-if="activeDropdown === tenant.id"
                                            class="absolute left-0 mt-2 w-56 bg-white rounded-2xl shadow-2xl border border-stone-200 py-2 z-50 text-right dir-rtl divide-y divide-stone-100"
                                        >
                                            <!-- View Details -->
                                            <div class="py-1">
                                                <Link
                                                    :href="adminPaths.tenant(tenant.id)"
                                                    class="flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-stone-700 hover:bg-stone-50 hover:text-stone-900 transition-colors"
                                                >
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                                    عرض تفاصيل المكتب
                                                </Link>
                                            </div>

                                            <!-- Extend Options -->
                                            <div class="py-1">
                                                <button
                                                    @click="openPlanModal(tenant)"
                                                    class="w-full text-right flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-blue-700 hover:bg-blue-50 transition-colors cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                                                    تحديد / تغيير خطة الاشتراك
                                                </button>
                                                <button
                                                    @click="extendSubscription(tenant.id, 30)"
                                                    class="w-full text-right flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-emerald-700 hover:bg-emerald-50 transition-colors cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                                    تمديد الاشتراك (+30 يومًا)
                                                </button>
                                                <button
                                                    @click="extendSubscription(tenant.id, 365)"
                                                    class="w-full text-right flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-emerald-800 hover:bg-emerald-50 transition-colors cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                                                    تمديد الاشتراك (+سنة كاملة)
                                                </button>
                                                <button
                                                    @click="extendModalTenant = tenant"
                                                    class="w-full text-right flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-stone-600 hover:bg-stone-50 transition-colors cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                                    تحديد أيام تمديد مخصصة...
                                                </button>
                                            </div>

                                            <!-- Activate / Cancel Status -->
                                            <div class="py-1">
                                                <button
                                                    @click="toggleStatus(tenant.id)"
                                                    class="w-full text-right flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold transition-colors cursor-pointer"
                                                    :class="tenant.status === 'active' ? 'text-amber-700 hover:bg-amber-50' : 'text-emerald-700 hover:bg-emerald-50'"
                                                >
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"></path></svg>
                                                    <span>{{ tenant.status === 'active' ? 'إلغاء / إيقاف الاشتراك' : 'تفعيل الاشتراك' }}</span>
                                                </button>
                                            </div>

                                            <!-- Delete Subscriber -->
                                            <div class="py-1">
                                                <button
                                                    @click="confirmDelete = tenant"
                                                    class="w-full text-right flex items-center gap-2.5 px-4 py-2.5 text-xs font-bold text-rose-600 hover:bg-rose-50 transition-colors cursor-pointer"
                                                >
                                                    <svg class="w-4 h-4 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                                    حذف المشترك نهائياً
                                                </button>
                                            </div>
                                        </div>
                                    </Transition>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="tenants.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100">
                    <p class="text-sm text-stone-500">
                        عرض {{ tenants.from }} - {{ tenants.to }} من {{ tenants.total }} مكتب
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in tenants.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                                :class="link.active ? 'bg-red-700 text-white font-bold' : 'text-stone-500 hover:bg-stone-100'"
                                v-html="link.label"
                                preserve-state
                            />
                            <span
                                v-else
                                class="px-3 py-1.5 text-sm text-stone-300"
                                v-html="link.label"
                            />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <!-- Custom Extension Days Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="extendModalTenant" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="extendModalTenant = null">
                    <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-right dir-rtl">
                        <div class="w-12 h-12 rounded-2xl bg-emerald-100 flex items-center justify-center mb-4">
                            <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-900 mb-1">تمديد اشتراك المكتب</h3>
                        <p class="text-xs text-stone-500 mb-4">المكتب: <strong class="text-stone-800">{{ extendModalTenant.name }}</strong></p>
                        
                        <div class="mb-5">
                            <label class="block text-xs font-bold text-stone-700 mb-2">أدخل عدد أيام التمديد المطلوب إضافة الاشتراك بها:</label>
                            <input
                                v-model.number="extendDays"
                                type="number"
                                min="1"
                                class="w-full px-4 py-3 bg-stone-50 border border-stone-300 rounded-xl text-stone-900 font-mono font-bold focus:outline-none focus:border-emerald-600 text-base"
                            />
                        </div>

                        <div class="flex items-center gap-3">
                            <button
                                @click="extendModalTenant = null"
                                class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors"
                            >
                                إلغاء
                            </button>
                            <button
                                @click="extendSubscription(extendModalTenant.id, extendDays)"
                                class="flex-1 px-4 py-3 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl text-sm font-bold transition-colors shadow-md shadow-emerald-200"
                            >
                                ⚡ تمديد الآن
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <!-- Assign Plan Modal -->
        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="planModalTenant" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="planModalTenant = null">
                    <div class="bg-white rounded-3xl p-6 max-w-md w-full shadow-2xl text-right dir-rtl">
                        <h3 class="text-lg font-bold text-stone-900 mb-1">تحديد نوع الاشتراك</h3>
                        <p class="text-xs text-stone-500 mb-5">حسب خطط التسعير — المكتب: <strong class="text-stone-800">{{ planModalTenant.name }}</strong></p>

                        <div class="space-y-2 mb-4">
                            <label
                                v-for="plan in plans"
                                :key="plan.id"
                                class="flex items-start gap-3 p-3 rounded-xl border cursor-pointer transition"
                                :class="Number(selectedPlanId) === Number(plan.id) ? 'border-red-600 bg-red-50/50' : 'border-stone-200 hover:border-stone-300'"
                            >
                                <input v-model="selectedPlanId" type="radio" :value="plan.id" class="mt-1 text-red-700" />
                                <div class="flex-1">
                                    <p class="text-sm font-black text-stone-900">{{ plan.name }}</p>
                                    <p class="text-[11px] text-stone-500 font-semibold">{{ plan.tagline }}</p>
                                    <p class="text-xs font-bold text-red-700 mt-1">
                                        <span v-if="Number(plan.price_yearly) === 0">مجاني</span>
                                        <span v-else>
                                            شهري: {{ Number(plan.price_monthly).toLocaleString() }} ج.م —
                                            سنوي: {{ Number(plan.price_yearly).toLocaleString() }} ج.م
                                        </span>
                                    </p>
                                </div>
                            </label>
                        </div>

                        <div class="mb-5">
                            <label class="block text-xs font-bold text-stone-700 mb-2">فترة الفوترة</label>
                            <select v-model="billingPeriod" class="w-full px-4 py-3 rounded-xl border border-stone-200 bg-stone-50 text-sm font-bold">
                                <option value="yearly">سنوي</option>
                                <option value="monthly">شهري</option>
                            </select>
                        </div>

                        <div class="flex items-center gap-3">
                            <button @click="planModalTenant = null" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50">إلغاء</button>
                            <button @click="updatePlan" class="flex-1 px-4 py-3 bg-red-700 hover:bg-red-800 text-white rounded-xl text-sm font-bold">حفظ الخطة</button>
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
                <div v-if="confirmDelete" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="confirmDelete = null">
                    <div class="bg-white rounded-3xl p-6 max-w-sm w-full shadow-2xl text-right dir-rtl">
                        <div class="w-14 h-14 rounded-2xl bg-rose-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-900 text-center mb-2">حذف المشترك نهائياً</h3>
                        <p class="text-xs text-stone-500 text-center mb-6">هل أنت متأكد من إلغاء اشتراك وحذف مكتب <strong class="text-stone-900 font-bold">{{ confirmDelete.name }}</strong>؟ سيتم إيقاف النطاق والتسجيلات الخاصة بالمكتب.</p>
                        <div class="flex items-center gap-3">
                            <button
                                @click="confirmDelete = null"
                                class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors"
                            >
                                إلغاء
                            </button>
                            <button
                                @click="deleteTenant(confirmDelete.id)"
                                class="flex-1 px-4 py-3 bg-rose-600 hover:bg-rose-700 text-white rounded-xl text-sm font-bold transition-colors shadow-md shadow-rose-200"
                            >
                                نعم، احذف المشترك
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>

        <AdminPasswordConfirm
            :show="showPasswordModal"
            :processing="passwordProcessing"
            :error="passwordErrorFromPage"
            title="تأكيد بكلمة مرور المدير"
            description="أي تعديل على المكاتب يتطلب إدخال كلمة مرور المدير للتأكيد."
            @close="showPasswordModal = false; pendingAction = null"
            @confirm="runWithPassword"
        />
    </AdminLayout>
</template>
