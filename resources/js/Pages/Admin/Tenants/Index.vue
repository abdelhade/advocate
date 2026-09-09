<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    tenants: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
let searchTimeout = null;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('admin.tenants.index'), { search: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
});

const confirmDelete = ref(null);

const deleteTenant = (id) => {
    router.delete(route('admin.tenants.destroy', id), {
        onSuccess: () => {
            confirmDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="إدارة المكاتب" />

    <AdminLayout>
        <template #title>إدارة المكاتب</template>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-sm text-stone-400">إدارة جميع مكاتب المحاماة المسجلة في المنصة</p>
            </div>
            <Link :href="route('admin.tenants.create')" class="inline-flex items-center gap-2 px-5 py-3 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-red-200 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة مكتب
            </Link>
        </div>

        <!-- Search & Filters -->
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 mb-6">
            <div class="relative">
                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="بحث بمعرّف المكتب..."
                    class="w-full pr-12 pl-4 py-3 rounded-xl border border-stone-200 bg-stone-50 text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-500 transition-all"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 overflow-hidden">
            <!-- Empty -->
            <div v-if="tenants.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                </div>
                <p class="text-stone-600 text-lg font-bold" v-if="search">لا توجد نتائج لـ "{{ search }}"</p>
                <p class="text-stone-600 text-lg font-bold" v-else>لا توجد مكاتب مسجلة</p>
                <p class="text-stone-400 text-sm mt-1">ابدأ بإضافة أول مكتب محاماة</p>
            </div>

            <div v-else>
                <table class="w-full">
                    <thead>
                        <tr class="bg-stone-50/80 border-b border-stone-100">
                            <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">#</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">اسم المكتب / المعرف</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">المحامي المسجل</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">الحالة والتجربة</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">النطاق</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">تاريخ التسجيل</th>
                            <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr
                            v-for="(tenant, index) in tenants.data"
                            :key="tenant.id"
                            class="hover:bg-red-50/30 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 text-sm text-stone-400 font-mono">{{ (tenants.current_page - 1) * tenants.per_page + index + 1 }}</td>
                            <td class="px-6 py-4">
                                <Link :href="route('admin.tenants.show', tenant.id)" class="block">
                                    <span class="text-sm font-bold text-stone-800 hover:text-red-700 transition-colors block">{{ tenant.name }}</span>
                                    <span class="text-xs text-stone-400 font-mono">ID: {{ tenant.id }}</span>
                                </Link>
                            </td>
                            <td class="px-6 py-4">
                                <div class="text-sm font-semibold text-stone-700">{{ tenant.owner_name }}</div>
                                <div class="text-xs text-stone-400">{{ tenant.email }} • {{ tenant.phone }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <div v-if="tenant.status === 'active'" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-emerald-50 text-emerald-700 text-xs font-bold border border-emerald-200">
                                    <span>🟢 مشترك نشط</span>
                                </div>
                                <div v-else-if="tenant.is_expired" class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-rose-50 text-rose-700 text-xs font-bold border border-rose-200">
                                    <span>🔴 انتهت الفترة التجريبية</span>
                                </div>
                                <div v-else class="inline-flex items-center gap-1 px-3 py-1 rounded-full bg-amber-50 text-amber-700 text-xs font-bold border border-amber-200">
                                    <span>⏳ تجريبي (متبقي {{ tenant.days_left }} يوم)</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex flex-wrap gap-1.5 dir-ltr justify-end">
                                    <a
                                        v-for="domain in tenant.domains"
                                        :key="domain"
                                        :href="'http://' + domain + ':8080'"
                                        target="_blank"
                                        class="inline-flex items-center gap-1 px-2.5 py-1 rounded-lg bg-slate-100 text-slate-700 hover:bg-amber-100 hover:text-amber-900 text-xs font-medium transition"
                                    >
                                        🌐 {{ domain }}
                                    </a>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-sm text-stone-500">{{ tenant.created_at }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <Link :href="route('admin.tenants.show', tenant.id)" class="p-2 rounded-lg text-stone-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="التفاصيل والاشتراك">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                    </Link>
                                    <button
                                        @click="confirmDelete = tenant.id"
                                        class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                                        title="حذف"
                                    >
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>

                <!-- Pagination -->
                <div v-if="tenants.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100">
                    <p class="text-sm text-stone-500">
                        عرض {{ tenants.from }} - {{ tenants.to }} من {{ tenants.total }}
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
                    <div class="bg-white rounded-2xl p-6 max-w-sm w-full shadow-2xl">
                        <div class="w-14 h-14 rounded-2xl bg-red-100 flex items-center justify-center mx-auto mb-4">
                            <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                        </div>
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف المكتب</h3>
                        <p class="text-sm text-stone-500 text-center mb-6">هل أنت متأكد من حذف المكتب <strong class="text-stone-800">{{ confirmDelete }}</strong>؟ سيتم حذف جميع بياناته ونطاقاته بشكل نهائي.</p>
                        <div class="flex items-center gap-3">
                            <button
                                @click="confirmDelete = null"
                                class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors"
                            >
                                إلغاء
                            </button>
                            <button
                                @click="deleteTenant(confirmDelete)"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors"
                            >
                                نعم، احذف
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
