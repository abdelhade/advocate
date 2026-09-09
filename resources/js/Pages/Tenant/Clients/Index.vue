<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    clients: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
let searchTimeout = null;

watch(search, (value) => {
    clearTimeout(searchTimeout);
    searchTimeout = setTimeout(() => {
        router.get(route('clients.index'), { search: value || undefined }, {
            preserveState: true,
            replace: true,
        });
    }, 400);
});

const confirmDelete = ref(null);

const deleteClient = (id) => {
    router.delete(route('clients.destroy', id), {
        onSuccess: () => {
            confirmDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="الموكلين" />

    <TenantLayout>
        <template #title>الموكلين</template>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-sm text-stone-500">إدارة موكلين المكتب وبياناتهم</p>
            </div>
            <Link :href="route('clients.create')" class="inline-flex items-center gap-2 px-5 py-3 bg-blue-700 hover:bg-blue-800 text-white text-sm font-bold rounded-xl transition-all duration-200 shadow-sm hover:shadow-blue-200">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة موكل
            </Link>
        </div>

        <!-- Search -->
        <div class="bg-white rounded-2xl border border-stone-200/80 p-4 mb-6 shadow-sm">
            <div class="relative">
                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="بحث باسم الموكل، رقم الهاتف، أو رقم الهوية..."
                    class="w-full pr-12 pl-4 py-3 rounded-xl border border-stone-200 bg-stone-50 text-stone-800 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all"
                />
            </div>
        </div>

        <!-- Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
            <!-- Empty State -->
            <div v-if="clients.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                </div>
                <p class="text-stone-600 text-lg font-bold" v-if="search">لا توجد نتائج لـ "{{ search }}"</p>
                <p class="text-stone-600 text-lg font-bold" v-else>لا يوجد موكلين مسجلين</p>
                <p class="text-stone-400 text-sm mt-1">ابدأ بإضافة موكلك الأول لإدارة قضاياه</p>
            </div>

            <!-- Data Table -->
            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-stone-50/80 border-b border-stone-100">
                                <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">#</th>
                                <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">الاسم</th>
                                <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">رقم الهاتف</th>
                                <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">تاريخ الإضافة</th>
                                <th class="text-right px-6 py-4 text-xs font-bold text-stone-400 uppercase tracking-wider">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100">
                            <tr
                                v-for="(client, index) in clients.data"
                                :key="client.id"
                                class="hover:bg-blue-50/30 transition-colors duration-150"
                            >
                                <td class="px-6 py-4 text-sm text-stone-400">{{ (clients.current_page - 1) * clients.per_page + index + 1 }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-700 flex items-center justify-center font-bold text-sm shrink-0">
                                            {{ client.name.charAt(0) }}
                                        </div>
                                        <div>
                                            <Link :href="route('clients.show', client.id)" class="text-sm font-bold text-stone-800 hover:text-blue-700 transition-colors">
                                                {{ client.name }}
                                            </Link>
                                            <p v-if="client.email" class="text-[11px] text-stone-400 mt-0.5" dir="ltr">{{ client.email }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="text-sm font-medium text-stone-600" dir="ltr">{{ client.phone || '—' }}</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-stone-500">{{ client.created_at }}</td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <Link :href="route('clients.show', client.id)" class="p-2 rounded-lg text-stone-400 hover:text-emerald-600 hover:bg-emerald-50 transition-colors" title="عرض">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"></path></svg>
                                        </Link>
                                        <Link :href="route('clients.edit', client.id)" class="p-2 rounded-lg text-stone-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="تعديل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </Link>
                                        <button
                                            @click="confirmDelete = client.id"
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
                </div>

                <!-- Pagination -->
                <div v-if="clients.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100">
                    <p class="text-sm text-stone-500">
                        عرض {{ clients.from }} - {{ clients.to }} من {{ clients.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in clients.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                                :class="link.active ? 'bg-blue-700 text-white font-bold' : 'text-stone-500 hover:bg-stone-100'"
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
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف الموكل</h3>
                        <p class="text-sm text-stone-500 text-center mb-6">هل أنت متأكد من حذف الموكل؟ سيتم حذف جميع القضايا والبيانات المرتبطة به. لا يمكن التراجع عن هذا الإجراء.</p>
                        <div class="flex items-center gap-3">
                            <button
                                @click="confirmDelete = null"
                                class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors"
                            >
                                إلغاء
                            </button>
                            <button
                                @click="deleteClient(confirmDelete)"
                                class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors"
                            >
                                نعم، احذف الموكل
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
