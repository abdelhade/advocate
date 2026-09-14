<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    expenses: Object,
    totalExpenses: Number,
    cases: Array,
    filters: Object,
});

const showCreateModal = ref(false);

const form = useForm({
    category: 'رسوم قضائية وتراخيص',
    amount: '',
    expense_date: new Date().toISOString().substring(0, 10),
    case_id: '',
});

const categories = [
    'رسوم قضائية وتراخيص',
    'مصروفات انتقال وسفر',
    'طباعة وتصوير مستندات',
    'أجور خبراء ومستشارين',
    'مصروفات إدارية ونثريات',
];

const openCreateModal = () => {
    form.reset();
    form.expense_date = new Date().toISOString().substring(0, 10);
    showCreateModal.value = true;
};

const submitForm = () => {
    form.post(route('expenses.store'), {
        onSuccess: () => showCreateModal.value = false,
    });
};

const deleteExpense = (exp) => {
    if (confirm('هل أنت متأكد من حذف هذا المصروف؟')) {
        router.delete(route('expenses.destroy', exp.id));
    }
};
</script>

<template>
    <Head title="المصروفات النثرية والقضائية" />

    <TenantLayout>
        <template #title>
            <span>📉 المصروفات النثرية والقضائية</span>
        </template>
        <template #actions>
            <button
                @click="openCreateModal"
                class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl shadow-md shadow-blue-200 transition-all flex items-center gap-2 text-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إضافة مصروف جديد
            </button>
        </template>

        <!-- Summary Card -->
        <div class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm flex items-center gap-5 mb-8 max-w-sm">
            <div class="w-12 h-12 rounded-xl bg-rose-50 text-rose-700 flex items-center justify-center text-xl font-bold">💸</div>
            <div>
                <p class="text-xs font-bold text-stone-400">إجمالي المصروفات المسجلة</p>
                <p class="text-2xl font-black text-rose-700 mt-1">{{ Number(totalExpenses).toLocaleString() }} <span class="text-xs font-normal">ر.س</span></p>
            </div>
        </div>

        <!-- Expenses Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-stone-700">
                    <thead class="bg-stone-50 text-xs font-bold text-stone-500 border-b border-stone-200">
                        <tr>
                            <th class="px-6 py-4">التصنيف</th>
                            <th class="px-6 py-4">القضية المرتبطة</th>
                            <th class="px-6 py-4">المبلغ</th>
                            <th class="px-6 py-4">المصروف بواسطة</th>
                            <th class="px-6 py-4">التاريخ</th>
                            <th class="px-6 py-4 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="exp in expenses.data" :key="exp.id" class="hover:bg-stone-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-stone-800">{{ exp.category }}</td>
                            <td class="px-6 py-4 text-stone-500 font-bold">
                                {{ exp.case ? `${exp.case.title} (${exp.case.case_number})` : 'مصروف عام للمكتب' }}
                            </td>
                            <td class="px-6 py-4 font-black text-rose-700">- {{ Number(exp.amount).toLocaleString() }} ر.س</td>
                            <td class="px-6 py-4 text-xs font-bold text-stone-600">{{ exp.paid_by?.name || 'غير محدد' }}</td>
                            <td class="px-6 py-4 text-xs text-stone-500">{{ new Date(exp.expense_date).toLocaleDateString('ar-EG') }}</td>
                            <td class="px-6 py-4 text-center">
                                <button @click="deleteExpense(exp)" class="p-1.5 text-stone-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!expenses.data || expenses.data.length === 0">
                            <td colspan="6" class="p-12 text-center text-stone-400">
                                <p class="font-bold text-stone-600">لا توجد مصروفات مسجلة</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Add Expense Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-stone-200 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-stone-100">
                    <h3 class="text-lg font-bold text-stone-800">إضافة بند مصروفات جديد</h3>
                    <button @click="showCreateModal = false" class="text-stone-400 hover:text-stone-600">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">تصنيف المصروف *</label>
                        <select v-model="form.category" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm">
                            <option v-for="cat in categories" :key="cat" :value="cat">{{ cat }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">المبلغ (ر.س) *</label>
                        <input v-model.number="form.amount" type="number" step="0.01" min="0.01" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm font-black text-rose-700" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">القضية المرتبطة (اختياري)</label>
                        <select v-model="form.case_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm">
                            <option value="">-- مصروف عام (غير مرتبط بقضية) --</option>
                            <option v-for="c in cases" :key="c.id" :value="c.id">{{ c.title }} ({{ c.case_number }})</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">تاريخ الصرف *</label>
                        <input v-model="form.expense_date" type="date" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm" />
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100">
                        <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-stone-600 hover:bg-stone-100">إلغاء</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm shadow-md shadow-blue-200">
                            تسجيل المصروف
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
