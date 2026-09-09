<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';

defineProps({
    admins: Array,
});

const confirmDelete = ref(null);

const deleteAdmin = (id) => {
    router.delete(route('admin.admins.destroy', id), {
        onSuccess: () => {
            confirmDelete.value = null;
        },
    });
};
</script>

<template>
    <Head title="إدارة المديرين" />

    <AdminLayout>
        <template #title>إدارة المديرين</template>

        <!-- Header -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4 mb-6">
            <div>
                <p class="text-sm text-stone-400">إدارة حسابات المديرين والصلاحيات</p>
            </div>
            <Link :href="route('admin.admins.create')" class="inline-flex items-center gap-2 px-5 py-3 bg-red-700 hover:bg-red-800 text-white text-sm font-bold rounded-xl transition-all duration-200 hover:shadow-lg hover:shadow-red-200 flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"></path></svg>
                إضافة مدير
            </Link>
        </div>

        <!-- Error Messages -->
        <div v-if="$page.props.errors?.error" class="mb-6">
            <div class="bg-red-50 border border-red-200 text-red-700 px-5 py-3.5 rounded-xl text-sm font-medium flex items-center gap-2">
                <svg class="w-5 h-5 text-red-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.34 16.5c-.77.833.192 2.5 1.732 2.5z"></path></svg>
                {{ $page.props.errors.error }}
            </div>
        </div>

        <!-- Admin Cards Grid -->
        <div v-if="admins.length === 0" class="bg-white rounded-2xl border border-stone-200/80 py-20 text-center">
            <div class="w-20 h-20 rounded-2xl bg-stone-100 flex items-center justify-center mx-auto mb-5">
                <svg class="w-10 h-10 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
            </div>
            <p class="text-stone-600 text-lg font-bold">لا يوجد مديرين</p>
        </div>

        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5">
            <div
                v-for="admin in admins"
                :key="admin.id"
                class="bg-white rounded-2xl border border-stone-200/80 p-6 hover:shadow-lg hover:shadow-stone-200/50 transition-all duration-300 group"
            >
                <div class="flex items-start justify-between mb-5">
                    <div class="w-14 h-14 rounded-2xl bg-gradient-to-br from-stone-100 to-stone-200 flex items-center justify-center">
                        <span class="text-xl font-black text-stone-500">{{ admin.name.charAt(0).toUpperCase() }}</span>
                    </div>
                    <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity">
                        <Link :href="route('admin.admins.edit', admin.id)" class="p-2 rounded-lg text-stone-400 hover:text-blue-600 hover:bg-blue-50 transition-colors" title="تعديل">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                        </Link>
                        <button
                            @click="confirmDelete = admin.id"
                            class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 transition-colors"
                            title="حذف"
                        >
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                        </button>
                    </div>
                </div>
                <h3 class="text-base font-bold text-stone-800">{{ admin.name }}</h3>
                <p class="text-sm text-stone-400 mt-0.5 font-mono" dir="ltr">@{{ admin.username }}</p>
                <div class="flex items-center gap-2 mt-4 pt-4 border-t border-stone-100">
                    <svg class="w-4 h-4 text-stone-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                    <span class="text-xs text-stone-400">{{ admin.created_at }}</span>
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
                        <h3 class="text-lg font-bold text-stone-800 text-center mb-2">حذف المدير</h3>
                        <p class="text-sm text-stone-500 text-center mb-6">هل أنت متأكد من حذف هذا الحساب؟ لا يمكن التراجع عن هذا الإجراء.</p>
                        <div class="flex items-center gap-3">
                            <button @click="confirmDelete = null" class="flex-1 px-4 py-3 border border-stone-200 rounded-xl text-sm font-semibold text-stone-600 hover:bg-stone-50 transition-colors">
                                إلغاء
                            </button>
                            <button @click="deleteAdmin(confirmDelete)" class="flex-1 px-4 py-3 bg-red-600 hover:bg-red-700 text-white rounded-xl text-sm font-bold transition-colors">
                                نعم، احذف
                            </button>
                        </div>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </AdminLayout>
</template>
