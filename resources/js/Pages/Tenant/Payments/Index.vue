<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
    payments: Object,
    stats: Object,
    clients: Array,
    invoices: Array,
    defaultPaymentNumber: String,
    filters: Object,
});

const showCreateModal = ref(false);

const form = useForm({
    payment_number: props.defaultPaymentNumber,
    client_id: '',
    invoice_id: '',
    amount: '',
    payment_method: 'cash',
    reference_number: '',
    payment_date: new Date().toISOString().substring(0, 10),
    notes: '',
});

const openCreateModal = () => {
    form.reset();
    form.payment_number = props.defaultPaymentNumber;
    form.payment_date = new Date().toISOString().substring(0, 10);
    showCreateModal.value = true;
};

const submitForm = () => {
    form.post(route('payments.store'), {
        onSuccess: () => showCreateModal.value = false,
    });
};

const deletePayment = (p) => {
    if (confirm(`هل أنت متأكد من إغلاق/حذف سند القبض ${p.payment_number}؟`)) {
        router.delete(route('payments.destroy', p.id));
    }
};

const methodLabels = {
    cash: 'نقداً',
    bank_transfer: 'تحويل بنكي',
    check: 'شيك مصدّق',
    card: 'بطاقة ائتمان',
};
</script>

<template>
    <Head title="سندات القبض المالية" />

    <TenantLayout>
        <template #title>
            <span>💵 سندات القبض المقبوضات المالية</span>
        </template>
        <template #actions>
            <button
                @click="openCreateModal"
                class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl shadow-md shadow-blue-200 transition-all flex items-center gap-2 text-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                تسجيل سند قبض جديد
            </button>
        </template>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl font-bold">💰</div>
                <div>
                    <p class="text-xs font-bold text-stone-400">إجمالي المقبوضات المحصلة</p>
                    <p class="text-xl font-black text-emerald-700 mt-1">{{ Number(stats.total_collected).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl font-bold">🧾</div>
                <div>
                    <p class="text-xs font-bold text-stone-400">إجمالي عدد سندات القبض</p>
                    <p class="text-xl font-black text-stone-800 mt-1">{{ stats.payments_count }}</p>
                </div>
            </div>
        </div>

        <!-- Payments Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-stone-700">
                    <thead class="bg-stone-50 text-xs font-bold text-stone-500 border-b border-stone-200">
                        <tr>
                            <th class="px-6 py-4">رقم السند</th>
                            <th class="px-6 py-4">الموكل</th>
                            <th class="px-6 py-4">الفاتورة المرتبطة</th>
                            <th class="px-6 py-4">طريقة الدفع</th>
                            <th class="px-6 py-4">المبلغ</th>
                            <th class="px-6 py-4">التاريخ</th>
                            <th class="px-6 py-4 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="p in payments.data" :key="p.id" class="hover:bg-stone-50/80 transition-colors">
                            <td class="px-6 py-4 font-black text-blue-700">{{ p.payment_number }}</td>
                            <td class="px-6 py-4 font-bold text-stone-800">{{ p.client?.name }}</td>
                            <td class="px-6 py-4 text-stone-500 font-bold">
                                {{ p.invoice ? p.invoice.invoice_number : 'قبض مباشر' }}
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-3 py-1 bg-stone-100 text-stone-700 font-bold rounded-lg text-xs border border-stone-200">
                                    {{ methodLabels[p.payment_method] || p.payment_method }}
                                </span>
                            </td>
                            <td class="px-6 py-4 font-black text-emerald-700">+ {{ Number(p.amount).toLocaleString() }} </td>
                            <td class="px-6 py-4 text-xs text-stone-500">{{ new Date(p.payment_date).toLocaleDateString('ar-EG') }}</td>
                            <td class="px-6 py-4 text-center">
                                <button @click="deletePayment(p)" class="p-1.5 text-stone-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                </button>
                            </td>
                        </tr>
                        <tr v-if="!payments.data || payments.data.length === 0">
                            <td colspan="7" class="p-12 text-center text-stone-400">
                                <p class="font-bold text-stone-600">لا توجد سندات قبض مسجلة</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Payment Modal -->
        <div v-if="showCreateModal" class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-stone-900/40 backdrop-blur-sm">
            <div class="bg-white rounded-2xl max-w-md w-full p-6 border border-stone-200 shadow-2xl space-y-4">
                <div class="flex justify-between items-center pb-3 border-b border-stone-100">
                    <h3 class="text-lg font-bold text-stone-800">تسجيل سند قبض جديد</h3>
                    <button @click="showCreateModal = false" class="text-stone-400 hover:text-stone-600">✕</button>
                </div>

                <form @submit.prevent="submitForm" class="space-y-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">رقم السند *</label>
                        <input v-model="form.payment_number" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm font-bold text-blue-700" />
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">الموكل *</label>
                        <select v-model="form.client_id" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm">
                            <option value="">-- اختر الموكل --</option>
                            <option v-for="cl in clients" :key="cl.id" :value="cl.id">{{ cl.name }}</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">الفاتورة المستهدفة (اختياري)</label>
                        <select v-model="form.invoice_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm">
                            <option value="">-- قبض مباشر (بدون فاتورة) --</option>
                            <option v-for="inv in invoices" :key="inv.id" :value="inv.id">
                                {{ inv.invoice_number }} (إجمالي: {{ inv.total_amount }} - المتبقي: {{ inv.total_amount - inv.paid_amount }})
                            </option>
                        </select>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">المبلغ المقبوض () *</label>
                            <input v-model.number="form.amount" type="number" step="0.01" min="0.01" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm font-black text-emerald-700" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">وسيلة السداد *</label>
                            <select v-model="form.payment_method" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm">
                                <option value="cash">نقداً</option>
                                <option value="bank_transfer">تحويل بنكي</option>
                                <option value="check">شيك</option>
                                <option value="card">بطاقة مدى/ائتمان</option>
                            </select>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">رقم المرجع / الشيك</label>
                            <input v-model="form.reference_number" type="text" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm" placeholder="اختياري" />
                        </div>
                        <div>
                            <label class="block text-xs font-bold text-stone-700 mb-1.5">تاريخ القبض *</label>
                            <input v-model="form.payment_date" type="date" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm" />
                        </div>
                    </div>

                    <div class="flex justify-end gap-3 pt-4 border-t border-stone-100">
                        <button type="button" @click="showCreateModal = false" class="px-5 py-2.5 rounded-xl text-sm font-bold text-stone-600 hover:bg-stone-100">إلغاء</button>
                        <button type="submit" :disabled="form.processing" class="px-6 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl text-sm shadow-md shadow-blue-200">
                            تسجيل السند
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </TenantLayout>
</template>
