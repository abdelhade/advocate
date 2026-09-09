<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tenant: Object,
});

const domainForm = useForm({
    domain: '',
});

const addDomain = () => {
    domainForm.post(route('admin.tenants.domains.add', props.tenant.id), {
        onSuccess: () => domainForm.reset(),
    });
};

const removingDomain = ref(null);

const removeDomain = (domainId) => {
    router.delete(route('admin.tenants.domains.remove', [props.tenant.id, domainId]), {
        onSuccess: () => {
            removingDomain.value = null;
        },
    });
};

const confirmDelete = ref(false);

const deleteTenant = () => {
    router.delete(route('admin.tenants.destroy', props.tenant.id));
};
</script>

<template>
    <Head :title="`تفاصيل المكتب: ${tenant.id}`" />

    <AdminLayout>
        <template #title>تفاصيل المكتب</template>

        <!-- Breadcrumb -->
        <div class="flex items-center gap-2 text-sm text-stone-400 mb-6">
            <Link :href="route('admin.tenants.index')" class="hover:text-red-700 transition-colors">المكاتب</Link>
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
            <span class="text-stone-600 font-medium">{{ tenant.id }}</span>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Main Info -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Tenant Details -->
                <div class="bg-white rounded-2xl border border-stone-200/80 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <h2 class="text-lg font-bold text-stone-800">بيانات المكتب والاشتراك</h2>
                        <div v-if="tenant.status === 'active'" class="px-3 py-1 bg-emerald-50 text-emerald-700 font-bold text-xs rounded-full border border-emerald-200">
                            🟢 اشتراك نشط
                        </div>
                        <div v-else class="px-3 py-1 bg-amber-50 text-amber-700 font-bold text-xs rounded-full border border-amber-200">
                            ⏳ فترة تجريبية (متبقي {{ tenant.days_left }} يوم)
                        </div>
                    </div>

                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">اسم المكتب</p>
                            <p class="text-base font-bold text-stone-800">{{ tenant.name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">معرّف المكتب (ID)</p>
                            <p class="text-base font-bold text-stone-800 font-mono" dir="ltr">{{ tenant.id }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">المحامي / المدير</p>
                            <p class="text-base font-bold text-stone-800">{{ tenant.owner_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">البريد والهاتف</p>
                            <p class="text-sm font-semibold text-stone-700">{{ tenant.email }} • {{ tenant.phone }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">تاريخ انتهاء التجربة</p>
                            <p class="text-sm font-bold text-amber-700 font-mono">{{ tenant.trial_ends_at }}</p>
                        </div>
                        <div>
                            <p class="text-xs font-semibold text-stone-400 uppercase tracking-wider mb-1">تاريخ الإنشاء</p>
                            <p class="text-sm font-bold text-stone-800">{{ tenant.created_at }}</p>
                        </div>
                    </div>

                    <!-- Actions Bar for Subscription -->
                    <div class="pt-5 border-t border-stone-100 flex flex-wrap items-center gap-3">
                        <span class="text-xs font-bold text-stone-500">إدارة التجربة والاشتراك:</span>
                        <button
                            @click="router.post(route('admin.tenants.extend_trial', tenant.id), { days: 7 })"
                            class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-lg border border-amber-200 transition"
                        >
                            + تمديد 7 أيام
                        </button>
                        <button
                            @click="router.post(route('admin.tenants.extend_trial', tenant.id), { days: 15 })"
                            class="px-3 py-1.5 bg-amber-50 hover:bg-amber-100 text-amber-800 text-xs font-bold rounded-lg border border-amber-200 transition"
                        >
                            + تمديد 15 يوم
                        </button>
                        <button
                            v-if="tenant.status !== 'active'"
                            @click="router.post(route('admin.tenants.activate', tenant.id))"
                            class="px-4 py-1.5 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg transition shadow-sm"
                        >
                            ⚡ تفعيل اشتراك كامل مدفوع
                        </button>
                    </div>
                </div>

                <!-- Domains -->
                <div class="bg-white rounded-2xl border border-stone-200/80 p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-stone-800">النطاقات</h2>
                            <p class="text-sm text-stone-400 mt-0.5">النطاقات المرتبطة بهذا المكتب</p>
                        </div>
                        <span class="text-xs font-bold text-stone-500 bg-stone-100 px-3 py-1 rounded-full">
                            {{ tenant.domains.length }} نطاق
                        </span>
                    </div>

                    <!-- Existing Domains -->
                    <div v-if="tenant.domains.length > 0" class="space-y-2 mb-6">
                        <div
                            v-for="domain in tenant.domains"
                            :key="domain.id"
                            class="flex items-center justify-between p-4 rounded-xl bg-stone-50 border border-stone-100 group hover:border-stone-200 transition-colors"
                        >
                            <div class="flex items-center gap-3">
                                <div class="w-9 h-9 rounded-lg bg-red-50 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9"></path></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-stone-800" dir="ltr">{{ domain.domain }}</p>
                                    <p class="text-[11px] text-stone-400">{{ domain.created_at }}</p>
                                </div>
                            </div>
                            <button
                                v-if="removingDomain === domain.id"
                                @click="removeDomain(domain.id)"
                                class="text-xs font-bold text-red-600 bg-red-50 px-3 py-1.5 rounded-lg border border-red-200 hover:bg-red-100 transition-colors"
                            >
                                تأكيد الحذف
                            </button>
                            <button
                                v-else
                                @click="removingDomain = domain.id"
                                class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 transition-colors opacity-0 group-hover:opacity-100"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                    <div v-else class="py-8 text-center mb-6">
                        <p class="text-stone-400 text-sm">لا توجد نطاقات مرتبطة</p>
                    </div>

                    <!-- Add Domain Form -->
                    <form @submit.prevent="addDomain" class="flex items-start gap-3 pt-5 border-t border-stone-100">
                        <div class="flex-1">
                            <input
                                type="text"
                                v-model="domainForm.domain"
                                placeholder="أدخل النطاق الجديد..."
                                dir="ltr"
                                class="w-full px-4 py-3 rounded-xl border border-stone-300 bg-stone-50 text-stone-900 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all text-left"
                                :class="{ 'border-red-500': domainForm.errors.domain }"
                            />
                            <p v-if="domainForm.errors.domain" class="mt-1 text-sm text-red-600">{{ domainForm.errors.domain }}</p>
                        </div>
                        <button
                            type="submit"
                            :disabled="domainForm.processing || !domainForm.domain"
                            class="px-5 py-3 bg-stone-800 hover:bg-stone-900 text-white text-sm font-bold rounded-xl transition-all disabled:opacity-40 disabled:cursor-not-allowed flex-shrink-0"
                        >
                            <svg v-if="domainForm.processing" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span v-else>إضافة نطاق</span>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div class="bg-white rounded-2xl border border-stone-200/80 p-6">
                    <h3 class="text-sm font-bold text-stone-800 mb-4">إجراءات سريعة</h3>
                    <div class="space-y-2">
                        <button
                            @click="confirmDelete = true"
                            class="w-full flex items-center gap-3 px-4 py-3 rounded-xl text-sm font-medium text-red-700 bg-red-50 hover:bg-red-100 border border-red-100 transition-colors"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            حذف المكتب
                        </button>
                    </div>
                </div>

                <!-- Info -->
                <div class="bg-stone-50 rounded-2xl border border-stone-200/80 p-6">
                    <h3 class="text-sm font-bold text-stone-800 mb-3">ملاحظات</h3>
                    <ul class="space-y-2 text-xs text-stone-500">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-stone-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            حذف المكتب سيؤدي إلى حذف جميع بياناته ونطاقاته.
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-stone-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            يمكنك إضافة عدة نطاقات لنفس المكتب.
                        </li>
                    </ul>
                </div>
            </div>
        </div>

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
                <div v-if="confirmDelete" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="confirmDelete = false">
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف المكتب</h3>
                        <p class="text-sm text-stone-500 text-center mb-6">هل أنت متأكد من حذف المكتب <strong class="text-stone-800">{{ tenant.id }}</strong>؟ سيتم حذف جميع البيانات والنطاقات بشكل نهائي.</p>
                        <div class="flex items-center gap-3">
                            <button @click="confirmDelete = false" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors">
                                إلغاء
                            </button>
                            <button @click="deleteTenant" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors">
                                نعم، احذف
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
