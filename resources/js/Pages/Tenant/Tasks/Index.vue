<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, useForm, router } from '@inertiajs/vue3';
import { computed, ref, watch } from 'vue';

const props = defineProps({
    tasks: Object,
    cases: Array,
    users: Array,
    filters: Object,
    stats: Object,
});

const search = ref(props.filters?.search || '');
const status = ref(props.filters?.status || '');
const priority = ref(props.filters?.priority || '');
const caseId = ref(props.filters?.case_id || '');
const sort = ref(props.filters?.sort || 'created_at');
const direction = ref(props.filters?.direction || 'desc');
const showFilters = ref(!!(props.filters?.status || props.filters?.priority || props.filters?.case_id));

let filterTimeout = null;

const filterParams = () => ({
    search: search.value || undefined,
    status: status.value || undefined,
    priority: priority.value || undefined,
    case_id: caseId.value || undefined,
    sort: sort.value !== 'created_at' ? sort.value : undefined,
    direction: direction.value !== 'desc' || sort.value !== 'created_at' ? direction.value : undefined,
});

const updateFilters = (immediate = false) => {
    clearTimeout(filterTimeout);
    const run = () => {
        router.get(route('tasks.index'), filterParams(), {
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

watch([search, status, priority, caseId], () => {
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

const hasActiveFilters = computed(() => !!(status.value || priority.value || caseId.value));

const showCreateModal = ref(false);
const editingTask = ref(null);

const form = useForm({
    title: '',
    description: '',
    case_id: '',
    assignee_id: '',
    priority: 'medium',
    due_date: '',
    status: 'pending',
});

const openCreateModal = () => {
    editingTask.value = null;
    form.reset();
    showCreateModal.value = true;
};

const openEditModal = (task) => {
    editingTask.value = task;
    form.title = task.title;
    form.description = task.description || '';
    form.case_id = task.case_id || '';
    form.assignee_id = task.assignee_id || '';
    form.priority = task.priority;
    form.due_date = task.due_date ? task.due_date.substring(0, 10) : '';
    form.status = task.status;
    showCreateModal.value = true;
};

const submitForm = () => {
    if (editingTask.value) {
        form.put(route('tasks.update', editingTask.value.id), {
            onSuccess: () => { showCreateModal.value = false; },
        });
    } else {
        form.post(route('tasks.store'), {
            onSuccess: () => { showCreateModal.value = false; },
        });
    }
};

const toggleTaskStatus = (task, newStatus) => {
    router.patch(route('tasks.update-status', task.id), { status: newStatus }, { preserveScroll: true });
};

const deleteTask = (task) => {
    if (confirm('هل أنت متأكد من حذف هذه المهمة؟')) {
        router.delete(route('tasks.destroy', task.id), { preserveScroll: true });
    }
};

const priorityLabels = {
    low: { text: 'منخفضة', class: 'bg-stone-100 text-stone-700 dark:bg-slate-800 dark:text-slate-300 border-stone-200 dark:border-slate-700' },
    medium: { text: 'متوسطة', class: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400 border-blue-200 dark:border-blue-800' },
    high: { text: 'عالية', class: 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400 border-amber-200 dark:border-amber-800' },
    urgent: { text: 'عاجلة', class: 'bg-rose-100 text-rose-700 dark:bg-rose-950/50 dark:text-rose-400 border-rose-200 dark:border-rose-800' },
};

const statusLabels = {
    pending: { text: 'قيد الانتظار', class: 'bg-amber-100 text-amber-700 dark:bg-amber-950/50 dark:text-amber-400' },
    in_progress: { text: 'قيد التنفيذ', class: 'bg-blue-100 text-blue-700 dark:bg-blue-950/50 dark:text-blue-400' },
    completed: { text: 'مكتملة', class: 'bg-emerald-100 text-emerald-700 dark:bg-emerald-950/50 dark:text-emerald-400' },
    cancelled: { text: 'ملغاة', class: 'bg-stone-100 text-stone-500 dark:bg-slate-800 dark:text-slate-400' },
};

const formatDueDate = (dateStr) => {
    if (!dateStr) return '—';
    return new Date(dateStr).toLocaleDateString('ar-EG');
};
</script>

<template>
    <Head title="المهام" />

    <TenantLayout>
        <template #title>المهام</template>

        <div class="flex items-center justify-between gap-4 mb-5">
            <nav class="flex items-center gap-2 text-sm text-stone-500 dark:text-slate-400">
                <Link :href="route('dashboard')" class="hover:text-blue-700 dark:hover:text-blue-400 transition-colors">لوحة التحكم</Link>
                <svg class="w-3.5 h-3.5 text-stone-300 dark:text-slate-600 rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path></svg>
                <span class="font-bold text-stone-800 dark:text-white">المهام</span>
            </nav>
            <button
                type="button"
                @click="openCreateModal"
                class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-blue-700 hover:bg-blue-800 text-white shadow-sm hover:shadow-blue-200 transition-all"
                title="إضافة مهمة جديدة"
                aria-label="إضافة مهمة جديدة"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
            </button>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 p-4 mb-5 shadow-sm">
            <div class="flex flex-col sm:flex-row gap-3">
                <div class="relative flex-1">
                    <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-stone-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                    <input
                        v-model="search"
                        type="text"
                        placeholder="بحث في عنوان المهمة..."
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
                    <span v-if="hasActiveFilters" class="w-2 h-2 rounded-full bg-blue-600"></span>
                </button>
            </div>

            <Transition
                enter-active-class="transition-all duration-200 overflow-hidden"
                enter-from-class="opacity-0 max-h-0"
                enter-to-class="opacity-100 max-h-48"
                leave-active-class="transition-all duration-150 overflow-hidden"
                leave-from-class="opacity-100 max-h-48"
                leave-to-class="opacity-0 max-h-0"
            >
                <div v-show="showFilters" class="grid grid-cols-1 md:grid-cols-3 gap-3 mt-3 pt-3 border-t border-stone-100 dark:border-slate-800">
                    <select
                        v-model="status"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الحالات</option>
                        <option value="pending">قيد الانتظار</option>
                        <option value="in_progress">قيد التنفيذ</option>
                        <option value="completed">مكتملة</option>
                        <option value="cancelled">ملغاة</option>
                    </select>
                    <select
                        v-model="priority"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع الأولويات</option>
                        <option value="urgent">عاجلة</option>
                        <option value="high">عالية</option>
                        <option value="medium">متوسطة</option>
                        <option value="low">منخفضة</option>
                    </select>
                    <select
                        v-model="caseId"
                        class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500 transition-all text-sm appearance-none"
                    >
                        <option value="">جميع القضايا</option>
                        <option v-for="c in cases" :key="c.id" :value="c.id">{{ c.title }} ({{ c.case_number }})</option>
                    </select>
                </div>
            </Transition>
        </div>

        <div class="bg-white dark:bg-slate-900 rounded-2xl border border-stone-200/80 dark:border-slate-800 shadow-sm overflow-hidden">
            <div v-if="!tasks.data || tasks.data.length === 0" class="py-20 text-center">
                <div class="w-20 h-20 rounded-2xl bg-stone-100 dark:bg-slate-800 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-10 h-10 text-stone-300 dark:text-slate-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                </div>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-if="search || hasActiveFilters">لا توجد نتائج مطابقة للبحث</p>
                <p class="text-stone-600 dark:text-slate-300 text-lg font-bold" v-else>لا توجد مهام مسجلة</p>
            </div>

            <div v-else>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="bg-stone-50/80 dark:bg-slate-800/50 border-b border-stone-100 dark:border-slate-800">
                                <th class="text-right px-4 py-3 w-12"></th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('title')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        العنوان
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('title') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('title') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">القضية</th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('due_date')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الموعد
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('due_date') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('due_date') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('priority')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الأولوية
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('priority') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('priority') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3">
                                    <button type="button" @click="toggleSort('status')" class="inline-flex items-center gap-1.5 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider hover:text-stone-700 dark:hover:text-slate-300">
                                        الحالة
                                        <span class="text-[10px] opacity-60" v-if="sortIcon('status') === 'asc'">▲</span>
                                        <span class="text-[10px] opacity-60" v-else-if="sortIcon('status') === 'desc'">▼</span>
                                        <span class="text-[10px] opacity-30" v-else>⇅</span>
                                    </button>
                                </th>
                                <th class="text-right px-6 py-3 text-xs font-bold text-stone-400 dark:text-slate-500 uppercase tracking-wider">إجراءات</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-stone-100 dark:divide-slate-800">
                            <tr
                                v-for="task in tasks.data"
                                :key="task.id"
                                class="hover:bg-blue-50/30 dark:hover:bg-slate-800/40 transition-colors duration-150"
                            >
                                <td class="px-4 py-4">
                                    <button
                                        type="button"
                                        @click="toggleTaskStatus(task, task.status === 'completed' ? 'pending' : 'completed')"
                                        class="w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all shrink-0"
                                        :class="task.status === 'completed' ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-stone-300 dark:border-slate-600 hover:border-blue-600'"
                                        title="تبديل الإنجاز"
                                    >
                                        <svg v-if="task.status === 'completed'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                                    </button>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="text-sm font-bold text-stone-800 dark:text-white"
                                        :class="{ 'line-through text-stone-400 dark:text-slate-500': task.status === 'completed' }"
                                    >
                                        {{ task.title }}
                                    </span>
                                    <p v-if="task.assignee" class="text-xs text-stone-500 dark:text-slate-400 mt-0.5">{{ task.assignee.name }}</p>
                                </td>
                                <td class="px-6 py-4 text-sm text-stone-600 dark:text-slate-300">
                                    <span v-if="task.case">{{ task.case.title }}</span>
                                    <span v-else class="text-stone-400">—</span>
                                </td>
                                <td class="px-6 py-4 text-sm text-stone-500 dark:text-slate-400">{{ formatDueDate(task.due_date) }}</td>
                                <td class="px-6 py-4">
                                    <span :class="['inline-flex px-2.5 py-1 text-xs font-bold rounded-lg border', priorityLabels[task.priority]?.class]">
                                        {{ priorityLabels[task.priority]?.text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span :class="['px-2.5 py-1 rounded-lg text-xs font-bold', statusLabels[task.status]?.class]">
                                        {{ statusLabels[task.status]?.text }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <button @click="openEditModal(task)" class="p-2 rounded-lg text-stone-400 hover:text-blue-600 hover:bg-blue-50 dark:hover:bg-blue-950/30 transition-colors" title="تعديل">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                                        </button>
                                        <button @click="deleteTask(task)" class="p-2 rounded-lg text-stone-400 hover:text-red-600 hover:bg-red-50 dark:hover:bg-rose-950/30 transition-colors" title="حذف">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div v-if="tasks.last_page > 1" class="flex items-center justify-between px-6 py-4 border-t border-stone-100 dark:border-slate-800">
                    <p class="text-sm text-stone-500 dark:text-slate-400">
                        عرض {{ tasks.from }} - {{ tasks.to }} من {{ tasks.total }}
                    </p>
                    <div class="flex items-center gap-1">
                        <template v-for="link in tasks.links" :key="link.label">
                            <Link
                                v-if="link.url"
                                :href="link.url"
                                class="px-3 py-1.5 rounded-lg text-sm transition-colors"
                                :class="link.active ? 'bg-blue-700 text-white font-bold' : 'text-stone-500 dark:text-slate-400 hover:bg-stone-100 dark:hover:bg-slate-800'"
                                v-html="link.label"
                                preserve-state
                            />
                            <span v-else class="px-3 py-1.5 text-sm text-stone-300" v-html="link.label" />
                        </template>
                    </div>
                </div>
            </div>
        </div>

        <div class="mt-4 flex flex-wrap items-center gap-x-5 gap-y-2 text-xs text-stone-500 dark:text-slate-400 px-1">
            <span>الإجمالي <strong class="text-stone-800 dark:text-white font-bold tabular-nums">{{ stats?.total ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>قيد الانتظار <strong class="text-amber-700 dark:text-amber-400 font-bold tabular-nums">{{ stats?.pending ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>قيد التنفيذ <strong class="text-blue-700 dark:text-blue-400 font-bold tabular-nums">{{ stats?.in_progress ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>مكتملة <strong class="text-emerald-700 dark:text-emerald-400 font-bold tabular-nums">{{ stats?.completed ?? 0 }}</strong></span>
            <span class="text-stone-300 dark:text-slate-700">·</span>
            <span>ملغاة <strong class="text-stone-600 dark:text-slate-300 font-bold tabular-nums">{{ stats?.cancelled ?? 0 }}</strong></span>
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
                <div v-if="showCreateModal" class="fixed inset-0 z-[100] flex items-center justify-center p-4 bg-black/40 backdrop-blur-sm" @click.self="showCreateModal = false">
                    <div class="bg-white dark:bg-slate-900 rounded-2xl max-w-lg w-full p-6 border border-stone-200 dark:border-slate-800 shadow-2xl space-y-4 max-h-[90vh] overflow-y-auto">
                        <div class="flex justify-between items-center pb-3 border-b border-stone-100 dark:border-slate-800">
                            <h3 class="text-lg font-bold text-stone-800 dark:text-white">{{ editingTask ? 'تعديل مهمة' : 'إضافة مهمة جديدة' }}</h3>
                            <button type="button" @click="showCreateModal = false" class="text-stone-400 hover:text-stone-600 dark:hover:text-slate-300 p-1">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>

                        <form @submit.prevent="submitForm" class="space-y-4">
                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">عنوان المهمة *</label>
                                <input v-model="form.title" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="مثال: إعداد مذكرة الدفاع..." />
                            </div>

                            <div>
                                <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">وصف التفاصيل</label>
                                <textarea v-model="form.description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="ملاحظات تفصيلية للمهمة..."></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">القضية المرتبطة</label>
                                    <select v-model="form.case_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        <option value="">— اختيار قضية —</option>
                                        <option v-for="c in cases" :key="c.id" :value="c.id">{{ c.title }} ({{ c.case_number }})</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">المكلّف بالمهمة</label>
                                    <select v-model="form.assignee_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        <option value="">— اختيار عضو —</option>
                                        <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                                    </select>
                                </div>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">الأولوية</label>
                                    <select v-model="form.priority" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                        <option value="low">منخفضة</option>
                                        <option value="medium">متوسطة</option>
                                        <option value="high">عالية</option>
                                        <option value="urgent">عاجلة</option>
                                    </select>
                                </div>
                                <div>
                                    <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">تاريخ الإنجاز المطلوب</label>
                                    <input v-model="form.due_date" type="date" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                                </div>
                            </div>

                            <div v-if="editingTask">
                                <label class="block text-xs font-bold text-stone-700 dark:text-slate-300 mb-1.5">حالة المهمة</label>
                                <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 dark:border-slate-700 bg-stone-50 dark:bg-slate-800 text-stone-800 dark:text-slate-100 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                    <option value="pending">قيد الانتظار</option>
                                    <option value="in_progress">قيد التنفيذ</option>
                                    <option value="completed">مكتملة</option>
                                    <option value="cancelled">ملغاة</option>
                                </select>
                            </div>

                            <div class="flex justify-end gap-3 pt-4 border-t border-stone-100 dark:border-slate-800">
                                <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-stone-600 dark:text-slate-300 hover:bg-stone-100 dark:hover:bg-slate-800">إلغاء</button>
                                <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm">
                                    {{ editingTask ? 'حفظ التعديلات' : 'إنشاء المهمة' }}
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </Transition>
        </Teleport>
    </TenantLayout>
</template>
