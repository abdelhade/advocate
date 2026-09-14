<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tenant: Object,
});

const showExtendModal = ref(false);
const extendDays = ref(30);
const confirmDeleteModal = ref(false);
const copiedSubdomain = ref(false);

const copySubdomain = () => {
    if (props.tenant?.subdomain_url) {
        navigator.clipboard.writeText(props.tenant.subdomain_url);
        copiedSubdomain.value = true;
        setTimeout(() => {
            copiedSubdomain.value = false;
        }, 2000);
    }
};

const handleExtendSubscription = () => {
    router.post(route('admin.tenants.extend', props.tenant.id), {
        days: extendDays.value,
    }, {
        onSuccess: () => {
            showExtendModal.value = false;
        }
    });
};

const toggleStatus = () => {
    router.post(route('admin.tenants.toggle_status', props.tenant.id));
};

const deleteTenant = () => {
    router.delete(route('admin.tenants.destroy', props.tenant.id));
};
</script>

<template>
    <Head :title="`تفاصيل المكتب: ${tenant.name}`" />

    <AdminLayout>
        <template #title>تفاصيل المكتب والاشتراك</template>

        <!-- Breadcrumbs & Actions Header -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 mb-6">
            <div class="flex items-center gap-2 text-sm text-stone-400">
                <Link :href="route('admin.tenants.index')" class="hover:text-red-700 transition-colors">المكاتب</Link>
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"></path></svg>
                <span class="text-stone-800 font-bold">{{ tenant.name }}</span>
            </div>

            <!-- Header Action Controls -->
            <div class="flex flex-wrap items-center gap-3">
                <a
                    :href="tenant.subdomain_url"
                    target="_blank"
                    class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl transition flex items-center gap-2 border border-stone-200"
                >
                    <svg class="w-4 h-4 text-stone-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                    <span>زيارة رابط المكتب</span>
                </a>

                <button
                    @click="showExtendModal = true"
                    class="px-4 py-2.5 bg-amber-600 hover:bg-amber-700 text-white text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>تمديد الاشتراك</span>
                </button>

                <button
                    @click="toggleStatus"
                    :class="[
                        'px-4 py-2.5 text-xs font-bold rounded-xl transition shadow-sm flex items-center gap-2',
                        tenant.status === 'active' ? 'bg-amber-100 text-amber-800 hover:bg-amber-200 border border-amber-300' : 'bg-emerald-600 hover:bg-emerald-700 text-white'
                    ]"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    <span>{{ tenant.status === 'active' ? 'إيقاف الاشتراك' : 'تفعيل الاشتراك' }}</span>
                </button>

                <button
                    @click="confirmDeleteModal = true"
                    class="px-4 py-2.5 bg-red-50 hover:bg-red-100 text-red-700 text-xs font-bold rounded-xl transition border border-red-200 flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                    <span>حذف المكتب</span>
                </button>
            </div>
        </div>

        <!-- Metric Stat Cards -->
        <div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">الموكلين</p>
                    <p class="text-2xl font-black text-stone-800 mt-0.5">{{ tenant.stats?.clients_count ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">القضايا</p>
                    <p class="text-2xl font-black text-stone-800 mt-0.5">{{ tenant.stats?.cases_count ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">المستندات</p>
                    <p class="text-2xl font-black text-stone-800 mt-0.5">{{ tenant.stats?.documents_count ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">الفواتير</p>
                    <p class="text-2xl font-black text-stone-800 mt-0.5">{{ tenant.stats?.invoices_count ?? 0 }}</p>
                </div>
            </div>

            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-xs flex items-center gap-4 col-span-2 md:col-span-1">
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-600 flex items-center justify-center shrink-0">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <div>
                    <p class="text-xs font-semibold text-stone-400">أعضاء الفريق</p>
                    <p class="text-2xl font-black text-stone-800 mt-0.5">{{ tenant.stats?.users_count ?? 0 }}</p>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
            <!-- Office Main Details Card -->
            <div class="lg:col-span-2 bg-white rounded-2xl border border-stone-200/80 p-6 shadow-xs">
                <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-6">
                    <h2 class="text-base font-bold text-stone-800 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-700" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5m3 0h1v-4a1 1 0 011-1h2a1 1 0 011 1v4h1m-4 0h4"></path></svg>
                        معلومات المكتب وحالة الاشتراك
                    </h2>
                    <span
                        :class="[
                            'px-3 py-1 text-xs font-extrabold rounded-full border',
                            tenant.status === 'active' ? 'bg-emerald-50 text-emerald-700 border-emerald-200' :
                            tenant.status === 'suspended' ? 'bg-red-50 text-red-700 border-red-200' :
                            'bg-amber-50 text-amber-700 border-amber-200'
                        ]"
                    >
                        {{ tenant.status === 'active' ? '🟢 اشتراك نشط' : tenant.status === 'suspended' ? '🔴 اشتراك موقوف' : '⏳ قيد المراجعة' }}
                    </span>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-6 mb-6">
                    <div>
                        <p class="text-xs font-medium text-stone-400 mb-1">اسم المكتب / الشراكة</p>
                        <p class="text-base font-bold text-stone-800">{{ tenant.name }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-stone-400 mb-1">معرّف المكتب الفرعي (Slug)</p>
                        <p class="text-base font-bold text-stone-800 font-mono" dir="ltr">{{ tenant.slug }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-stone-400 mb-1">تاريخ بدء الاشتراك</p>
                        <p class="text-sm font-bold text-stone-800 font-mono">{{ tenant.start_date }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-stone-400 mb-1">تاريخ انتهاء الاشتراك</p>
                        <div class="flex items-center gap-2">
                            <span class="text-sm font-bold text-stone-800 font-mono">{{ tenant.end_date }}</span>
                            <span class="text-xs px-2 py-0.5 rounded-md font-bold bg-stone-100 text-stone-600">
                                متبقي {{ tenant.days_left }} يوم
                            </span>
                        </div>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-stone-400 mb-1">البريد الإلكتروني الرسمي</p>
                        <p class="text-sm font-bold text-stone-800" dir="ltr">{{ tenant.email }}</p>
                    </div>

                    <div>
                        <p class="text-xs font-medium text-stone-400 mb-1">رقم الهاتف</p>
                        <p class="text-sm font-bold text-stone-800" dir="ltr">{{ tenant.phone }}</p>
                    </div>
                </div>

                <!-- Subdomain URL Box -->
                <div class="p-4 rounded-xl bg-stone-50 border border-stone-200/70 flex items-center justify-between gap-4">
                    <div class="overflow-hidden">
                        <p class="text-xs font-semibold text-stone-400 mb-1">رابط الدخول الخاص بالمكتب</p>
                        <a :href="tenant.subdomain_url" target="_blank" class="text-sm font-bold text-red-700 hover:underline truncate block" dir="ltr">
                            {{ tenant.subdomain_url }}
                        </a>
                    </div>
                    <button
                        @click="copySubdomain"
                        class="px-3 py-2 bg-white hover:bg-stone-100 text-stone-700 text-xs font-bold rounded-lg border border-stone-200 transition shrink-0 flex items-center gap-1.5"
                    >
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"></path></svg>
                        <span>{{ copiedSubdomain ? 'تم النسخ!' : 'نسخ الرابط' }}</span>
                    </button>
                </div>
            </div>

            <!-- Owner & Primary Contact Side Card -->
            <div class="bg-white rounded-2xl border border-stone-200/80 p-6 shadow-xs flex flex-col justify-between">
                <div>
                    <div class="flex items-center justify-between pb-4 border-b border-stone-100 mb-5">
                        <h3 class="text-base font-bold text-stone-800 flex items-center gap-2">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            صاحب المكتب / المالك
                        </h3>
                        <span class="px-2.5 py-0.5 bg-amber-50 text-amber-700 text-xs font-bold rounded-md border border-amber-200">المدير الرئيسي</span>
                    </div>

                    <div class="space-y-4">
                        <div>
                            <p class="text-xs font-medium text-stone-400 mb-1">الاسم الكامل</p>
                            <p class="text-base font-bold text-stone-800">{{ tenant.owner_name }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-stone-400 mb-1">البريد الإلكتروني للمالك</p>
                            <p class="text-sm font-semibold text-stone-700 font-mono" dir="ltr">{{ tenant.owner_email }}</p>
                        </div>

                        <div>
                            <p class="text-xs font-medium text-stone-400 mb-1">رقم التواصل</p>
                            <p class="text-sm font-semibold text-stone-700 font-mono" dir="ltr">{{ tenant.owner_phone }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-stone-100">
                    <p class="text-xs text-stone-400 leading-relaxed">
                        المالك الرئيسي يمتلك كامل صلاحيات إدارة المكاتب إضافة وإلغاء أعضاء الفريق.
                    </p>
                </div>
            </div>
        </div>

        <!-- Team Members Section -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-xs overflow-hidden mb-8">
            <div class="p-6 border-b border-stone-100 flex items-center justify-between">
                <div>
                    <h3 class="text-base font-bold text-stone-800">أعضاء الفريق والمستخدمين</h3>
                    <p class="text-xs text-stone-400 mt-0.5">قائمة المحامين والموظفين المسجلين في هذا المكتب</p>
                </div>
                <span class="text-xs font-bold bg-stone-100 text-stone-600 px-3 py-1 rounded-full">
                    {{ tenant.users?.length ?? 0 }} عضو
                </span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right border-collapse text-sm">
                    <thead>
                        <tr class="bg-stone-50 text-stone-500 font-semibold border-b border-stone-200/70 text-xs">
                            <th class="py-3.5 px-6">المستخدم</th>
                            <th class="py-3.5 px-6">البريد الإلكتروني</th>
                            <th class="py-3.5 px-6">رقم الهاتف</th>
                            <th class="py-3.5 px-6">الدور</th>
                            <th class="py-3.5 px-6">تاريخ الانضمام</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="user in tenant.users" :key="user.id" class="hover:bg-stone-50/60 transition-colors">
                            <td class="py-4 px-6 font-bold text-stone-800">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-full bg-stone-200 text-stone-700 flex items-center justify-center font-bold text-xs">
                                        {{ user.name.charAt(0) }}
                                    </div>
                                    <span>{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="py-4 px-6 text-stone-600 font-mono text-xs" dir="ltr">{{ user.email }}</td>
                            <td class="py-4 px-6 text-stone-600 font-mono text-xs" dir="ltr">{{ user.phone }}</td>
                            <td class="py-4 px-6">
                                <span v-if="user.is_owner" class="px-2.5 py-1 bg-amber-50 text-amber-700 font-bold text-xs rounded-full border border-amber-200">
                                    👑 مالك المكتب
                                </span>
                                <span v-else class="px-2.5 py-1 bg-stone-100 text-stone-600 font-medium text-xs rounded-full">
                                    عضو / محامي
                                </span>
                            </td>
                            <td class="py-4 px-6 text-stone-500 text-xs font-mono">{{ user.joined_at }}</td>
                        </tr>
                        <tr v-if="!tenant.users || tenant.users.length === 0">
                            <td colspan="5" class="py-8 text-center text-stone-400 text-sm">
                                لا يوجد مستخدمين مضافين لهذا المكتب حالياً.
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

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
    </AdminLayout>
</template>
