<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
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

const search = ref('');
const roleFilter = ref('');
const showFilters = ref(false);
const sort = ref('name');
const direction = ref('asc');

const form = useForm({
    name: '',
    email: '',
    phone: '',
    password: '',
    permissions: [],
});

const selectedCount = computed(() => form.permissions.length);

const stats = computed(() => {
    const list = props.users;
    const owners = list.filter((u) => u.is_owner).length;
    return {
        total: list.length,
        owners,
        members: list.length - owners,
    };
});

const hasActiveFilters = computed(() => !!roleFilter.value);

const toggleSort = (column) => {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = column;
        direction.value = 'asc';
    }
};

const sortIcon = (column) => {
    if (sort.value !== column) return 'none';
    return direction.value === 'asc' ? 'asc' : 'desc';
};

const filteredUsers = computed(() => {
    let list = [...props.users];
    const q = search.value.trim().toLowerCase();
    if (q) {
        list = list.filter(
            (u) =>
                (u.name && u.name.toLowerCase().includes(q)) ||
                (u.email && u.email.toLowerCase().includes(q)),
        );
    }
    if (roleFilter.value === 'owners') {
        list = list.filter((u) => u.is_owner);
    } else if (roleFilter.value === 'members') {
        list = list.filter((u) => !u.is_owner);
    }

    const col = sort.value;
    const dir = direction.value === 'asc' ? 1 : -1;
    list.sort((a, b) => {
        let av = a[col];
        let bv = b[col];
        if (col === 'joined_at') {
            av = av || '';
            bv = bv || '';
        } else {
            av = (av || '').toString().toLowerCase();
            bv = (bv || '').toString().toLowerCase();
        }
        if (av < bv) return -1 * dir;
        if (av > bv) return 1 * dir;
        return 0;
    });

    return list;
});

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
        <template #title>فريق العمل</template>

        <!-- Breadcrumb + add -->
        <div class="flex items-center justify-between gap-4 mb-5">
            <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
                <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-bold text-stone-800 dark:text-white">فريق العمل</span>
            </nav>
            <button
                v-if="canManageUsers"
                type="button"
                @click="openCreateModal"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-700 hover:bg-blue-800 text-white shadow-sm hover:shadow-blue-200 transition-all"
                title="إضافة مستخدم جديد للمكتب"
                aria-label="إضافة مستخدم جديد للمكتب"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        <!-- Search + filters toggle -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-4 mb-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="بحث بالاسم أو البريد الإلكتروني..."
                        class="w-full pr-12 pl-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                    />
                </div>
                <button
                    type="button"
                    @click="showFilters = !showFilters"
                    class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border text-sm font-bold transition-all shrink-0"
                    :class="showFilters || hasActiveFilters
                        ? 'border-blue-200 dark:border-blue-800 bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400'
                        : 'border-stone-200 dark:border-slate-700 bg-white dark:bg-slate-800 text-stone-600 dark:text-slate-300 hover:bg-stone-50 dark:hover:bg-slate-700'"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    فلاتر
                    <span
                        v-if="hasActiveFilters"
                        class="w-2 h-2 rounded-full bg-blue-600"
                    ></span>
                </button>
            </div>

            <Transition
                enter-active-class="transition-all duration-200 overflow-hidden"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-40"
                leave-active-class="transition-all duration-150 overflow-hidden"
                leave-from-class="opacity-100 max-h-40"
                leave-to-class="opacity-0 max-h-0"
            >
                <div v-show="showFilters" class="mt-3 pt-3 border-t border-stone-100 dark:border-slate-800">
                    <select
                        v-model="roleFilter"
                        class="w-full sm:max-w-xs px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الأعضاء</option>
                        <option value="owners">مالكين المكتب</option>
                        <option value="members">أعضاء الفريق</option>
                    </select>
                </div>
            </Transition>
        </div>

        <!-- Table -->
        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div v-if="users.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                </div>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold">لا يوجد مستخدمين مضافين لهذا المكتب حتى الآن.</p>
            </div>

            <div v-else-if="filteredUsers.length === 0" class="py-20 text-center">
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold">لا توجد نتائج مطابقة للبحث</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full text-right">
                    <thead>
                        <tr class="bg-stone-50/80 dark:bg-slate-800/50 border-b border-stone-100 dark:border-slate-800">
                            <th class="text-right px-6 py-3">
                                <button type="button" @click="toggleSort('name')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                    الاسم
                                    <span class="text-[10px] opacity-60" v-if="sortIcon('name') === 'asc'">▲</span>
                                    <span class="text-[10px] opacity-60" v-else-if="sortIcon('name') === 'desc'">▼</span>
                                    <span class="text-[10px] opacity-30" v-else>⇅</span>
                                </button>
                            </th>
                            <th class="text-right px-6 py-3">
                                <button type="button" @click="toggleSort('email')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                    البريد الإلكتروني
                                    <span class="text-[10px] opacity-60" v-if="sortIcon('email') === 'asc'">▲</span>
                                    <span class="text-[10px] opacity-60" v-else-if="sortIcon('email') === 'desc'">▼</span>
                                    <span class="text-[10px] opacity-30" v-else>⇅</span>
                                </button>
                            </th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">رقم الهاتف</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">الصفة بالمكتب</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">الصلاحيات</th>
                            <th class="text-right px-6 py-3">
                                <button type="button" @click="toggleSort('joined_at')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                    تاريخ الانضمام
                                    <span class="text-[10px] opacity-60" v-if="sortIcon('joined_at') === 'asc'">▲</span>
                                    <span class="text-[10px] opacity-60" v-else-if="sortIcon('joined_at') === 'desc'">▼</span>
                                    <span class="text-[10px] opacity-30" v-else>⇅</span>
                                </button>
                            </th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-slate-800 text-sm">
                        <tr
                            v-for="user in filteredUsers"
                            :key="user.id"
                            class="hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 font-bold text-stone-900 dark:text-white">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-blue-100 dark:bg-blue-950/50 text-blue-700 dark:text-blue-400 flex items-center justify-center font-black text-sm">
                                        {{ user.name ? user.name.charAt(0) : 'م' }}
                                    </div>
                                    <span>{{ user.name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4 text-stone-600 dark:text-slate-300 font-mono text-xs">{{ user.email }}</td>
                            <td class="px-6 py-4 text-stone-600 dark:text-slate-300 font-medium dir-ltr text-right">{{ user.phone }}</td>
                            <td class="px-6 py-4">
                                <span
                                    v-if="user.is_owner"
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-extrabold bg-amber-50 dark:bg-amber-950/40 text-amber-800 dark:text-amber-400 border border-amber-200 dark:border-amber-800"
                                >
                                    مالك المكتب
                                </span>
                                <span
                                    v-else
                                    class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-blue-50 dark:bg-blue-950/40 text-blue-700 dark:text-blue-400 border border-blue-200 dark:border-blue-800"
                                >
                                    عضو فريق
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span v-if="user.is_owner" class="text-xs font-bold text-amber-700 dark:text-amber-400">كل الصلاحيات</span>
                                <span v-else class="text-xs font-bold text-stone-600 dark:text-slate-300">{{ user.permissions_count || 0 }} صلاحية</span>
                            </td>
                            <td class="px-6 py-4 text-stone-400 dark:text-slate-500 text-xs font-mono">{{ user.joined_at }}</td>
                            <td class="px-6 py-4">
                                <template v-if="!user.is_owner">
                                    <div class="flex items-center justify-end gap-3">
                                        <button
                                            v-if="canManageUsers"
                                            @click="openEditModal(user)"
                                            class="text-xs font-bold text-stone-600 dark:text-slate-300 hover:text-blue-700 dark:hover:text-blue-400 transition"
                                        >
                                            تعديل / صلاحيات
                                        </button>
                                        <button
                                            v-if="confirmDeleteId !== user.id"
                                            @click="confirmDeleteId = user.id"
                                            class="text-xs font-bold text-rose-600 hover:text-rose-800 dark:text-rose-400 transition"
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
                                                class="text-xs font-bold text-stone-400 hover:text-stone-600 dark:hover:text-slate-300"
                                            >
                                                إلغاء
                                            </button>
                                        </div>
                                    </div>
                                </template>
                                <span v-else class="text-xs text-stone-300 dark:text-slate-600 font-bold">-</span>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Compact aggregated stats -->
        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500 dark:text-slate-400 px-1">
            <span>الإجمالي <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ stats.total }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>مالكين <strong class="text-amber-700 dark:text-amber-400 font-bold tabular-nums">{{ stats.owners }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>أعضاء <strong class="text-blue-700 dark:text-blue-400 font-bold tabular-nums">{{ stats.members }}</strong></span>
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
