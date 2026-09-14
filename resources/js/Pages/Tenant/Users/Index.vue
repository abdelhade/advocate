<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { computed, ref } from 'vue';

const props = defineProps({
    users: {
        type: Array,
        default: () => [],
    },
    permissionMatrix: {
        type: Array,
        default: () => [],
    },
    permissionPresets: {
        type: Array,
        default: () => [],
    },
    canManageUsers: {
        type: Boolean,
        default: false,
    },
});

const isModalOpen = ref(false);
const isEditMode = ref(false);
const editingUserId = ref(null);
const confirmDeleteId = ref(null);

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    permissions: [],
});

const selectedCount = computed(() => form.permissions.length);

const openCreateModal = () => {
    isEditMode.value = false;
    editingUserId.value = null;
    form.reset();
    form.permissions = [];
    form.clearErrors();
    isModalOpen.value = true;
};

const openEditModal = (user) => {
    isEditMode.value = true;
    editingUserId.value = user.id;
    form.clearErrors();
    form.name = user.name;
    form.email = user.email;
    form.phone = user.phone === '-' ? '' : (user.phone || '');
    form.password = '';
    form.permissions = [...(user.permissions || [])];
    isModalOpen.value = true;
};

const closeModal = () => {
    isModalOpen.value = false;
    isEditMode.value = false;
    editingUserId.value = null;
    form.reset();
    form.permissions = [];
};

const togglePermission = (name) => {
    const index = form.permissions.indexOf(name);
    if (index === -1) {
        form.permissions.push(name);
    } else {
        form.permissions.splice(index, 1);
    }
};

const isChecked = (name) => form.permissions.includes(name);

const applyPreset = (preset) => {
    form.permissions = [...(preset.permissions || [])];
};

const clearPermissions = () => {
    form.permissions = [];
};

const selectGroup = (group) => {
    const names = Object.keys(group.permissions || {});
    const set = new Set(form.permissions);
    names.forEach((name) => set.add(name));
    form.permissions = [...set];
};

const clearGroup = (group) => {
    const names = new Set(Object.keys(group.permissions || {}));
    form.permissions = form.permissions.filter((name) => !names.has(name));
};

const submitUser = () => {
    if (isEditMode.value && editingUserId.value) {
        form.put(route('tenant.users.update', editingUserId.value), {
            onSuccess: () => closeModal(),
        });
        return;
    }

    form.post(route('tenant.users.store'), {
        onSuccess: () => closeModal(),
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
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-8">
            <div>
                <h1 class="text-2xl font-black text-stone-900 tracking-tight">فريق العمل والمستخدمين</h1>
                <p class="text-xs font-bold text-stone-500 mt-1">إدارة المحامين والمستشارين وأعضاء فريق العمل بالمكتب مع صلاحيات مفصّلة</p>
            </div>
            <button
                v-if="canManageUsers"
                @click="openCreateModal"
                class="px-5 py-3 bg-red-700 hover:bg-red-800 text-white font-bold text-sm rounded-xl shadow-lg shadow-red-700/20 hover:shadow-red-700/30 transition-all duration-300 flex items-center justify-center gap-2 cursor-pointer"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                <span>إضافة مستخدم جديد للمكتب</span>
            </button>
        </div>

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
                            <th class="px-6 py-4">الصلاحيات</th>
                            <th class="px-6 py-4">تاريخ الانضمام</th>
                            <th class="px-6 py-4 text-left">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-sm">
                        <tr v-if="users.length === 0">
                            <td colspan="7" class="px-6 py-12 text-center text-stone-400 text-xs font-bold">
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
                                    مالك المكتب
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 text-blue-700 border border-blue-200"
                                >
                                    عضو فريق
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="user.is_owner" class="text-xs font-bold text-amber-700">كل الصلاحيات</span>
                                <span v-else class="text-xs font-bold text-stone-600">{{ user.permissions_count || 0 }} صلاحية</span>
                            </td>
                            <td class="px-6 py-4 text-stone-400 text-xs font-mono">{{ user.joined_at }}</td>
                            <td class="px-6 py-4 text-left">
                                <template v-if="!user.is_owner">
                                    <div class="flex items-center justify-end gap-3">
                                        <button
                                            v-if="canManageUsers"
                                            @click="openEditModal(user)"
                                            class="text-xs font-bold text-stone-600 hover:text-red-700 transition"
                                        >
                                            تعديل / صلاحيات
                                        </button>
                                        <button
                                            v-if="confirmDeleteId !== user.id"
                                            @click="confirmDeleteId = user.id"
                                            class="text-xs font-bold text-rose-600 hover:text-rose-800 transition"
                                        >
                                            إزالة
                                        </button>
                                        <div v-else class="flex items-center gap-2">
                                            <button
                                                @click="removeUser(user.id)"
                                                class="text-xs font-bold text-white bg-rose-600 px-2.5 py-1 rounded-lg hover:bg-rose-700"
                                            >
                                                تأكيد
                                            </button>
                                            <button
                                                @click="confirmDeleteId = null"
                                                class="text-xs font-bold text-stone-400 hover:text-stone-600"
                                            >
                                                إلغاء
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <span v-else class="text-xs text-stone-300 font-bold">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div v-if="isModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/40 backdrop-blur-xs">
            <div class="bg-white rounded-3xl max-w-4xl w-full max-h-[92vh] overflow-y-auto p-6 shadow-2xl border border-stone-200 dir-rtl">
                <div class="flex items-center justify-between mb-5 pb-3 border-b border-stone-100 sticky top-0 bg-white z-10">
                    <div>
                        <h3 class="text-lg font-black text-stone-900">
                            {{ isEditMode ? 'تعديل مستخدم وصلاحياته' : 'إضافة عضو / محامي للمكتب' }}
                        </h3>
                        <p class="text-[11px] text-stone-400 font-bold mt-1">حدّد الصلاحيات من المصفوفة أو اختر قالب جاهز</p>
                    </div>
                    <button @click="closeModal" class="text-stone-400 hover:text-stone-600 text-xl font-bold">✕</button>
                </div>

                <form @submit.prevent="submitUser" class="space-y-5">
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
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
                                :disabled="isEditMode"
                                placeholder="lawyer@example.com"
                                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 font-medium disabled:bg-stone-100 disabled:text-stone-500"
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
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">
                                {{ isEditMode ? 'كلمة مرور جديدة (اختياري)' : 'كلمة المرور (اختياري)' }}
                            </label>
                            <input
                                v-model="form.password"
                                type="password"
                                :placeholder="isEditMode ? 'اتركها فارغة للإبقاء على الحالية' : 'افتراضياً: 00000000'"
                                class="w-full px-4 py-3 bg-stone-50 border border-stone-200 rounded-xl text-stone-900 text-sm focus:outline-none focus:ring-2 focus:ring-red-500/20 focus:border-red-600 font-medium"
                            />
                            <p v-if="form.errors.password" class="mt-1 text-xs font-bold text-rose-600">{{ form.errors.password }}</p>
                        </div>
                    </div>

                    <div class="rounded-2xl border border-stone-200 bg-stone-50/60 p-4 space-y-4">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
                            <div>
                                <h4 class="text-sm font-black text-stone-900">مصفوفة الصلاحيات</h4>
                                <p class="text-[11px] text-stone-500 font-bold mt-0.5">المحدد: {{ selectedCount }} صلاحية</p>
                            </div>
                            <div class="flex flex-wrap items-center gap-2">
                                <button
                                    v-for="preset in permissionPresets"
                                    :key="preset.key"
                                    type="button"
                                    @click="applyPreset(preset)"
                                    class="px-3 py-1.5 rounded-lg text-[11px] font-bold border border-stone-200 bg-white text-stone-700 hover:border-red-300 hover:text-red-700 transition"
                                >
                                    {{ preset.label }}
                                </button>
                                <button
                                    type="button"
                                    @click="clearPermissions"
                                    class="px-3 py-1.5 rounded-lg text-[11px] font-bold border border-stone-200 bg-white text-stone-500 hover:text-rose-600 transition"
                                >
                                    مسح الكل
                                </button>
                            </div>
                        </div>

                        <p v-if="form.errors.permissions" class="text-xs font-bold text-rose-600">{{ form.errors.permissions }}</p>

                        <div class="space-y-3">
                            <div
                                v-for="group in permissionMatrix"
                                :key="group.key"
                                class="bg-white rounded-xl border border-stone-200 overflow-hidden"
                            >
                                <div class="px-4 py-2.5 bg-stone-50 border-b border-stone-100 flex items-center justify-between gap-3">
                                    <span class="text-xs font-extrabold text-stone-800">{{ group.label }}</span>
                                    <div class="flex items-center gap-2">
                                        <button type="button" @click="selectGroup(group)" class="text-[10px] font-bold text-red-700 hover:underline">تحديد الكل</button>
                                        <button type="button" @click="clearGroup(group)" class="text-[10px] font-bold text-stone-400 hover:underline">إلغاء</button>
                                    </div>
                                </div>
                                <div class="p-3 flex flex-wrap gap-2">
                                    <label
                                        v-for="(label, name) in group.permissions"
                                        :key="name"
                                        class="inline-flex items-center gap-2 px-3 py-2 rounded-lg border cursor-pointer transition text-xs font-bold"
                                        :class="isChecked(name)
                                            ? 'bg-red-50 border-red-300 text-red-800'
                                            : 'bg-white border-stone-200 text-stone-600 hover:border-stone-300'"
                                    >
                                        <input
                                            type="checkbox"
                                            class="rounded border-stone-300 text-red-700 focus:ring-red-500"
                                            :checked="isChecked(name)"
                                            @change="togglePermission(name)"
                                        />
                                        <span>{{ label }}</span>
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="pt-2 flex items-center justify-end gap-3 border-t border-stone-100 sticky bottom-0 bg-white pb-1">
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
                            <span>
                                {{ form.processing
                                    ? (isEditMode ? 'جاري الحفظ...' : 'جاري الإضافة...')
                                    : (isEditMode ? 'حفظ التعديلات' : 'حفظ وإضافة للمكتب') }}
                            </span>
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
