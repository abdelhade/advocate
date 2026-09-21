<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    caseTypes: Array,
    stats: Object,
    filters: Object,
});

const search = ref(props.filters?.search || '');
const sort = ref(props.filters?.sort || 'name');
const direction = ref(props.filters?.direction || 'asc');

let filterTimeout = null;

const filterParams = () => ({
    search: search.value || undefined,
    sort: sort.value !== 'name' ? sort.value : undefined,
    direction: direction.value !== 'asc' || sort.value !== 'name' ? direction.value : undefined,
});

const updateFilters = (immediate = false) => {
    clearTimeout(filterTimeout);
    const run = () => {
        router.get(route('case-types.index'), filterParams(), {
            preserveState: true,
            replace: true,
        });
    };
    if (immediate) {
        run();
    } else {
        filterTimeout = setTimeout(run, 400);
    }
};

watch(search, () => {
    updateFilters();
});

const toggleSort = (column) => {
    if (sort.value === column) {
        direction.value = direction.value === 'asc' ? 'desc' : 'asc';
    } else {
        sort.value = column;
        direction.value = 'asc';
    }
    updateFilters(true);
};

const sortIcon = (column) => {
    if (sort.value !== column) return 'none';
    return direction.value === 'asc' ? 'asc' : 'desc';
};

const showModal = ref(false);
const editingType = ref(null);

const form = useForm({
    name: '',
    color: '#3b82f6',
});

const colorPresets = [
    '#3b82f6', '#ef4444', '#10b981', '#f59e0b', '#8b5cf6',
    '#ec4899', '#06b6d4', '#f97316', '#6366f1', '#14b8a6',
];

const openCreate = () => {
    editingType.value = null;
    form.reset();
    form.clearErrors();
    form.color = '#3b82f6';
    showModal.value = true;
};

const openEdit = (type) => {
    editingType.value = type;
    form.clearErrors();
    form.name = type.name;
    form.color = type.color || '#3b82f6';
    showModal.value = true;
};

const submitForm = () => {
    if (editingType.value) {
        form.put(route('case-types.update', editingType.value.id), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    } else {
        form.post(route('case-types.store'), {
            preserveScroll: true,
            onSuccess: () => {
                showModal.value = false;
                form.reset();
            },
        });
    }
};

const deleteType = (type) => {
    if (confirm(`هل أنت متأكد من حذف نوع القضية "${type.name}"؟`)) {
        router.delete(route('case-types.destroy', type.id), { preserveScroll: true });
    }
};
</script>

<template>
    <Head title="أنواع القضايا" />

    <TenantLayout>
        <template #title>أنواع القضايا</template>

        <div class="flex items-center justify-between gap-4 mb-5">
            <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
                <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-bold text-stone-800 dark:text-white">أنواع القضايا</span>
            </nav>
            <button
                type="button"
                @click="openCreate"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-700 hover:bg-blue-800 text-white shadow-sm hover:shadow-blue-200 transition-all"
                title="إضافة نوع جديد"
                aria-label="إضافة نوع جديد"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-4 mb-5 shadow-sm">
            <div class="relative">
                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                <input
                    v-model="search"
                    type="text"
                    placeholder="بحث بالاسم..."
                    class="w-full pr-12 pl-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 placeholder-stone-400 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm"
                />
            </div>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div v-if="!caseTypes || caseTypes.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"></path></svg>
                </div>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-if="search">لا توجد نتائج مطابقة للبحث</p>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-else>لا توجد أنواع قضايا مسجلة</p>
            </div>

            <div v-else class="overflow-x-auto">
                <table class="w-full">
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
                            <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">اللون</th>
                            <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">إجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                        <tr
                            v-for="type in caseTypes"
                            :key="type.id"
                            class="hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors duration-150"
                        >
                            <td class="px-6 py-4 text-sm font-bold text-stone-800 dark:text-white">{{ type.name }}</td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <div
                                        class="w-6 h-6 rounded-lg border border-stone-200 dark:border-slate-700 shrink-0"
                                        :style="{ backgroundColor: type.color || '#3b82f6' }"
                                    ></div>
                                    <span class="text-xs font-mono text-stone-500 dark:text-slate-400" dir="ltr">{{ type.color || '#3b82f6' }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-2">
                                    <button @click="openEdit(type)" class="p-2 rounded-lg text-stone-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors" title="تعديل">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                    </button>
                                    <button @click="deleteType(type)" class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-rose-950/30 transition-colors" title="حذف">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500 dark:text-slate-400 px-1">
            <span>الإجمالي <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ stats?.total ?? 0 }}</strong></span>
        </div>

        <Teleport to="body">
            <Transition
                enter-active-class="transition-all duration-200"
                enter-from-class="opacity-0"
                enter-to-class="opacity-100"
                leave-active-class="transition-all duration-200"
                leave-from-class="opacity-100"
                leave-to-class="opacity-0"
            >
                <div v-if="showModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showModal = false">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-md w-full p-6 border border-stone-200 dark:border-slate-800 shadow-2xl space-y-4">
                        <div class="flex justify-between items-center pb-3 border-b border-stone-100 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-stone-800 dark:text-white">{{ editingType ? 'تعديل نوع القضية' : 'إضافة نوع قضية جديد' }}</h3>
                            <button type="button" @click="showModal = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-slate-300 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">اسم نوع القضية *</label>
                                <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="مثال: جنائي، مدني، تجاري..." />
                                <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">اللون المميز</label>
                                <div class="flex flex-wrap gap-2 mb-2">
                                    <button
                                        v-for="color in colorPresets"
                                        :key="color"
                                        type="button"
                                        @click="form.color = color"
                                        class="w-8 h-8 rounded-lg border-2 transition-all"
                                        :class="form.color === color ? 'border-stone-800 dark:border-white scale-110 shadow-md' : 'border-stone-200 dark:border-slate-700 hover:scale-105'"
                                        :style="{ backgroundColor: color }"
                                    ></button>
                                </div>
                                <input v-model="form.color" type="color" class="w-full h-10 rounded-xl border border-stone-200 dark:border-slate-700 cursor-pointer" />
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-slate-800">
                                <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-stone-600 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-800">إلغاء</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm">
                                    {{ editingType ? 'حفظ التعديلات' : 'إضافة النوع' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
