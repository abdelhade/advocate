<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    caseTypes: Array,
});

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
    form.color = '#3b82f6';
    showModal.value = true;
};

const openEdit = (type) => {
    editingType.value = type;
    form.name = type.name;
    form.color = type.color || '#3b82f6';
    showModal.value = true;
};

const submitForm = () => {
    if (editingType.value) {
        form.put(route('case-types.update', editingType.value.id), {
            onSuccess: () => showModal.value = false,
        });
    } else {
        form.post(route('case-types.store'), {
            onSuccess: () => showModal.value = false,
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
    <Head title="إدارة أنواع القضايا" />

    <TenantLayout>
        <template #title>⚖️ إدارة أنواع القضايا</template>
        <template #actions>
            <button
                @click="openCreate"
                class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl shadow-md shadow-blue-200 transition-all flex items-center gap-2 text-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة نوع جديد
            </button>
        </template>

        <div class="max-w-3xl">
            <!-- Description -->
            <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 mb-6">
                <p class="text-sm text-blue-800 font-medium">
                    أنشئ وعدّل أنواع القضايا المختلفة التي يعمل بها مكتبك. سيتم عرض هذه الأنواع عند إضافة قضية جديدة.
                </p>
            </div>

            <!-- Case Types List -->
            <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
                <div class="divide-y divide-stone-100">
                    <div
                        v-for="type in caseTypes"
                        :key="type.id"
                        class="p-5 flex items-center justify-between hover:bg-stone-50 transition-colors"
                    >
                        <div class="flex items-center gap-3">
                            <div class="w-4 h-4 rounded-full shrink-0 border border-stone-200" :style="{ backgroundColor: type.color || '#3b82f6' }"></div>
                            <span class="text-sm font-bold text-stone-800">{{ type.name }}</span>
                        </div>
                        <div class="flex items-center gap-1">
                            <button @click="openEdit(type)" class="p-2 text-stone-400 hover:text-blue-600 rounded-lg hover:bg-blue-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"></path></svg>
                            </button>
                            <button @click="deleteType(type)" class="p-2 text-stone-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                            </button>
                        </div>
                    </div>

                    <div v-if="!caseTypes || caseTypes.length === 0" class="p-12 text-center text-stone-400">
                        <div class="w-16 h-16 mx-auto mb-4 bg-stone-100 rounded-full flex items-center justify-center text-2xl">⚖️</div>
                        <p class="font-bold text-stone-600 text-base">لا توجد أنواع قضايا مسجلة</p>
                        <p class="text-xs text-stone-400 mt-1">اضغط على "إضافة نوع جديد" لإنشاء أول نوع قضية</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Create/Edit Modal -->
        <div v-if="showModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-stone-200 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-stone-100">
                    <h3 class="text-lg font-bold text-stone-800">{{ editingType ? 'تعديل نوع القضية' : 'إضافة نوع قضية جديد' }}</h3>
                    <button @click="showModal = false" class="text-stone-400 hover:text-stone-600">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">اسم نوع القضية *</label>
                        <input v-model="form.name" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" placeholder="مثال: جنائي، مدني، تجاري..." />
                        <p v-if="form.errors.name" class="mt-1 text-sm text-red-600">{{ form.errors.name }}</p>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">اللون المميز</label>
                        <div class="flex flex-wrap gap-2 mb-2">
                            <button
                                v-for="color in colorPresets"
                                :key="color"
                                type="button"
                                @click="form.color = color"
                                class="w-8 h-8 rounded-lg border-2 transition-all"
                                :class="form.color === color ? 'border-stone-800 scale-110 shadow-md' : 'border-stone-200 hover:scale-105'"
                                :style="{ backgroundColor: color }"
                            ></button>
                        </div>
                        <input v-model="form.color" type="color" class="w-full h-10 rounded-xl border border-stone-200 cursor-pointer" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100">
                        <button type="button" @click="showModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-stone-600 hover:bg-stone-100">إلغاء</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm shadow-md shadow-blue-200">
                            {{ editingType ? 'حفظ التعديلات' : 'إضافة النوع' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
