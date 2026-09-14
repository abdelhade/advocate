<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
});

const isModalOpen = ref(false);
const confirmDeleteId = ref(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
});

const openModal = () => {
    form.reset();
    form.clearErrors();
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    form.reset();
};

const submitUser = () => {
    form.post(route('tenant.users.store'), {
        onSuccess: () => {
            closeModal();
        },
    });
};

const removeUser = (userId) => {
    router.delete(route('tenant.users.destroy', userId), {
        onSuccess: () => {
            confirmDeleteId.value = null;
        },
    });
};
</script>

<template>
    <Head title="فريق العمل والمستخدمين" />

    <TenantLayout>
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-black text-stone-900 tracking-tight">فريق العمل والمستخدمين</h1>
                <p class="text-xs font-bold text-stone-500 mt-1">إدارة المحامين والمستشارين وأعضاء فريق العمل بالمكتب</p>
            </div>
            <button
                @click="openModal"
                class="px-5 py-3 bg-red-700 hover:bg-red-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-red-700/20 hover:shadow-red-700/30 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>إضافة مستخدم جديد للمكتب</span>
            </button>
        </div>

        <!-- Users Table Card -->
        <div class="bg-white rounded-3xl border border-stone-200/80 overflow-hidden shadow-xl shadow-stone-200/50">
            <div class="px-6 py-5 border-b border-stone-100 flex items-center justify-between bg-stone-50/50">
                <span class="text-xs font-bold text-stone-500">إجمالي أعضاء الفريق: <span class="text-stone-900 font-extrabold">{{ users.length }}</span></span>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="bg-stone-50/80 border-b border-stone-100 text-xs font-bold text-stone-400 uppercase tracking-wider">
                            <th class="px-6 py-4">الاسم</th>
                            <th class="px-6 py-4">البريد الإلكتروني</th>
                            <th class="px-6 py-4">رقم الهاتف</th>
                            <th class="px-6 py-4">الصفة بالمكتب</th>
                            <th class="px-6 py-4">تاريخ الانضمام</th>
                            <th class="px-6 py-4 text-left">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-sm">
                        <tr v-if="users.length === 0">
                            <td colspan="6" class="px-6 py-12 text-center text-stone-400 text-xs font-bold">
                                لا يوجد مستخدمين مضافين لهذا المكتب حتى الآن.
                            </td>
                        </tr>
                        <tr
                            v-for="user in users"
                            :key="user.id"
                            class="hover:bg-red-50/20 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 font-bold text-stone-900">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-red-100 text-red-700 flex items-center justify-center font-black text-sm">
                                        {{ user.name ? user.name.charAt(0) : 'م' }}
                                    </div>
                                    <span>{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-stone-600 font-mono text-xs">{{ user.email }}</td>
                            <td class="px-6 py-4 text-stone-600 font-medium dir-ltr text-right">{{ user.phone }}</td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="user.is_owner"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 text-amber-800 border border-amber-200"
                                >
                                    👑 مالك المكتب
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200"
                                >
                                    ⚖️ محامي / عضو فريق
                                </span>
                            </td>
                            <td class="px-6 py-4 text-stone-400 text-xs font-mono">{{ user.joined_at }}</td>
                            <td class="px-6 py-4 text-left">
                                <template v-if="!user.is_owner">
                                    <button
                                        v-if="confirmDeleteId !== user.id"
                                        @click="confirmDeleteId = user.id"
                                        class="text-xs font-bold text-rose-600 hover:text-rose-800 transition"
                                    >
                                        إزالة من المكتب
                                    </button>
                                    <div v-else class="flex items-center gap-2">
                                        <button
                                            @click="removeUser(user.id)"
                                            class="text-xs font-bold text-white bg-rose-600 px-2.5 py-1 rounded-lg hover:bg-rose-700"
                                        >
                                            تأكيد الإزالة
                                        </button>
                                        <button
                                            @click="confirmDeleteId = null"
                                            class="text-xs font-bold text-stone-400 hover:text-stone-600"
                                        >
                                            إلغاء
                                        </button>
                                    </div>
                                </template>
                                <span v-else class="text-xs text-stone-300 font-bold">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add User Modal -->
        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-md w-full p-6 shadow-2xl border border-stone-200 dir-rtl">
                <div class="flex items-center justify-between mb-5 pb-3 border-b border-stone-100">
                    <h3 class="text-lg font-black text-stone-900">إضافة عضو / محامي للمكتب</h3>
                    <button @click="closeModal" class="text-stone-400 hover:text-stone-600 text-xl font-bold">✕</button>
                </div>

                <form @submit.prevent="submitUser" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">الاسم الكامل *</label>
                        <input
                            v-model="form.name"
                            type="text"
                            required
                            placeholder="أ. خالد الأحمد"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 font-medium"
                        />
                        <p v-if="form.errors.name" class="mt-1 text-xs font-bold text-rose-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">البريد الإلكتروني *</label>
                        <input
                            v-model="form.email"
                            type="email"
                            required
                            placeholder="lawyer@example.com"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 font-medium"
                        />
                        <p v-if="form.errors.email" class="mt-1 text-xs font-bold text-rose-600">{{ form.errors.email }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">رقم الهاتف</label>
                        <input
                            v-model="form.phone"
                            type="text"
                            placeholder="0501234567"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 font-medium"
                        />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">كلمة المرور (اختياري)</label>
                        <input
                            v-model="form.password"
                            type="password"
                            placeholder="افتراضياً: 00000000"
                            class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 font-medium"
                        />
                        <p class="mt-1 text-[11px] text-stone-400">إذا تركتها فارغة، ستكون كلمة المرور الافتراضية هي 00000000</p>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-stone-100">
                        <button
                            type="button"
                            @click="closeModal"
                            class="px-5 py-2.5 rounded-xl border border-stone-200 text-stone-600 font-bold text-xs hover:bg-stone-50"
                        >
                            إلغاء
                        </button>
                        <button
                            type="submit"
                            :disabled="form.processing"
                            class="px-6 py-2.5 bg-red-700 hover:bg-red-800 text-white font-bold text-xs rounded-xl shadow-md disabled:opacity-50 flex items-center gap-2"
                        >
                            <svg v-if="form.processing" class="w-4 h-4 animate-spin text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                            <span>{{ form.processing ? 'جاري الإضافة...' : 'حفظ وإضافة للمكتب' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
