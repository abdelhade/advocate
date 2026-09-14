<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    tasks: Object,
    cases: Array,
    users: Array,
    filters: Object,
});

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
            onSuccess: () => showCreateModal.value = false,
        });
    } else {
        form.post(route('tasks.store'), {
            onSuccess: () => showCreateModal.value = false,
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

const filterStatus = (status) => {
    router.get(route('tasks.index'), { ...props.filters, status: status || undefined }, { preserveState: true });
};

const priorityLabels = {
    low: { text: 'منخفضة', class: 'bg-stone-100 text-stone-700 border-stone-200' },
    medium: { text: 'متوسطة', class: 'bg-blue-50 text-blue-700 border-blue-200' },
    high: { text: 'عالية', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    urgent: { text: 'عاجلة', class: 'bg-rose-50 text-rose-700 border-rose-200' },
};

const statusLabels = {
    pending: { text: 'قيد الانتظار', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    in_progress: { text: 'قيد التنفيذ', class: 'bg-blue-50 text-blue-700 border-blue-200' },
    completed: { text: 'مكتملة', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    cancelled: { text: 'ملغاة', class: 'bg-stone-100 text-stone-500 border-stone-200' },
};
</script>

<template>
    <Head title="إدارة المهام والأعمال" />

    <TenantLayout>
        <template #title>
            <span>📋 المهام والأعمال القضائية</span>
        </template>
        <template #actions>
            <button
                @click="openCreateModal"
                class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl shadow-md shadow-blue-200 transition-all flex items-center gap-2 text-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                مهمة جديدة
            </button>
        </template>

        <!-- Status Filter Tabs -->
        <div class="flex flex-wrap gap-2 mb-6 border-b border-stone-200 pb-4">
            <button
                @click="filterStatus('')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all border"
                :class="!filters.status ? 'bg-blue-700 text-white border-blue-700 shadow-sm' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50'"
            >
                جميع المهام
            </button>
            <button
                @click="filterStatus('pending')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all border"
                :class="filters.status === 'pending' ? 'bg-amber-600 text-white border-amber-600 shadow-sm' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50'"
            >
                قيد الانتظار
            </button>
            <button
                @click="filterStatus('in_progress')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all border"
                :class="filters.status === 'in_progress' ? 'bg-blue-600 text-white border-blue-600 shadow-sm' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50'"
            >
                قيد التنفيذ
            </button>
            <button
                @click="filterStatus('completed')"
                class="px-4 py-2 rounded-xl text-xs font-bold transition-all border"
                :class="filters.status === 'completed' ? 'bg-emerald-600 text-white border-emerald-600 shadow-sm' : 'bg-white text-stone-600 border-stone-200 hover:bg-stone-50'"
            >
                مكتملة
            </button>
        </div>

        <!-- Task List -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
            <div class="divide-y divide-stone-100">
                <div
                    v-for="task in tasks.data"
                    :key="task.id"
                    class="p-5 hover:bg-stone-50/80 transition-colors flex flex-col md:flex-row md:items-center justify-between gap-4"
                >
                    <div class="flex items-start gap-4 flex-1">
                        <!-- Checkbox Status Toggle -->
                        <button
                            @click="toggleTaskStatus(task, task.status === 'completed' ? 'pending' : 'completed')"
                            class="mt-1 w-6 h-6 rounded-lg border-2 flex items-center justify-center transition-all shrink-0"
                            :class="task.status === 'completed' ? 'bg-emerald-600 border-emerald-600 text-white' : 'border-stone-300 hover:border-blue-600'"
                        >
                            <svg v-if="task.status === 'completed'" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                        </button>

                        <div class="space-y-1">
                            <h3
                                class="text-base font-bold text-stone-800"
                                :class="{ 'line-through text-stone-400': task.status === 'completed' }"
                            >
                                {{ task.title }}
                            </h3>
                            <p v-if="task.description" class="text-xs text-stone-500 line-clamp-2">{{ task.description }}</p>
                            <div class="flex flex-wrap items-center gap-3 text-xs text-stone-500 pt-1">
                                <span v-if="task.case" class="flex items-center gap-1 font-bold text-blue-700 bg-blue-50 px-2.5 py-0.5 rounded-lg border border-blue-100">
                                    ⚖️ {{ task.case.title }} ({{ task.case.case_number }})
                                </span>
                                <span v-if="task.assignee" class="flex items-center gap-1">
                                    👤 المكلّف: {{ task.assignee.name }}
                                </span>
                                <span v-if="task.due_date" class="flex items-center gap-1 text-stone-500">
                                    📅 الموعد: {{ new Date(task.due_date).toLocaleDateString('ar-EG') }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 shrink-0">
                        <span :class="['px-3 py-1 text-xs font-bold rounded-lg border', priorityLabels[task.priority]?.class]">
                            أولوية {{ priorityLabels[task.priority]?.text }}
                        </span>
                        <span :class="['px-3 py-1 text-xs font-bold rounded-lg border', statusLabels[task.status]?.class]">
                            {{ statusLabels[task.status]?.text }}
                        </span>
                        <div class="flex items-center gap-1">
                            <button @click="openEditModal(task)" class="p-2 text-stone-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button @click="deleteTask(task)" class="p-2 text-stone-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div v-if="!tasks.data || tasks.data.length === 0" class="p-12 text-center text-stone-400">
                    <div class="w-16 h-16 mx-auto mb-4 bg-stone-100 rounded-full flex items-center justify-center text-2xl">📋</div>
                    <p class="font-bold text-stone-600 text-base">لا توجد مهام مسجلة حالياً</p>
                    <p class="text-xs text-stone-400 mt-1">اضغط على "مهمة جديدة" لإضافة أول مهمة للعمل عليها</p>
                </div>
            </div>
        </div>

        <!-- Task Create/Edit Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-lg w-full p-6 border border-stone-200 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-stone-100">
                    <h3 class="text-lg font-bold text-stone-800">{{ editingTask ? 'تعديل مهمة' : 'إضافة مهمة جديدة' }}</h3>
                    <button @click="showCreateModal = false" class="text-stone-400 hover:text-stone-600">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">عنوان المهمة *</label>
                        <input v-model="form.title" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="مثال: إعداد مذكرة الدفاع..." />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">وصف التفاصيل</label>
                        <textarea v-model="form.description" rows="3" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="ملاحظات تفصيلية للمهمة..."></textarea>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">القضية المرتبطة</label>
                            <select v-model="form.case_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">-- اختيار قضية --</option>
                                <option v-for="c in cases" :key="c.id" :value="c.id">{{ c.title }} ({{ c.case_number }})</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">المكلّف بالمهمة</label>
                            <select v-model="form.assignee_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="">-- اختيار عضو --</option>
                                <option v-for="u in users" :key="u.id" :value="u.id">{{ u.name }}</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">الأولوية</label>
                            <select v-model="form.priority" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                                <option value="low">منخفضة</option>
                                <option value="medium">متوسطة</option>
                                <option value="high">عالية</option>
                                <option value="urgent">عاجلة جداً</option>
                            </select>
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">تاريخ الإنجاز المطلوب</label>
                            <input v-model="form.due_date" type="date" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                        </div>
                    </div>

                    <div v-if="editingTask" class="pt-2">
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">حالة المهمة</label>
                        <select v-model="form.status" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="pending">قيد الانتظار</option>
                            <option value="in_progress">قيد التنفيذ</option>
                            <option value="completed">مكتملة</option>
                            <option value="cancelled">ملغاة</option>
                        </select>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100">
                        <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-stone-600 hover:bg-stone-100">إلغاء</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm shadow-md shadow-blue-200">
                            {{ editingTask ? 'حفظ التعديلات' : 'إنشاء المهمة' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
