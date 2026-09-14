<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, useForm, Link } from '@inertiajs/vue3';
import { computed } from 'vue';

const props = defineProps({
    clients: Array,
    cases: Array,
    defaultInvoiceNumber: String,
});

const form = useForm({
    client_id: '',
    case_id: '',
    invoice_number: props.defaultInvoiceNumber,
    due_date: '',
    discount_amount: 0,
    tax_amount: 0,
    items: [
        { description: 'أتعاب الترافع والدفاع القانوني', quantity: 1, unit_price: 0 },
    ],
});

const filteredCases = computed(() => {
    if (!form.client_id) return props.cases;
    return props.cases.filter(c => c.client_id == form.client_id);
});

const addItem = () => {
    form.items.push({ description: '', quantity: 1, unit_price: 0 });
};

const removeItem = (index) => {
    if (form.items.length > 1) {
        form.items.splice(index, 1);
    }
};

const subtotal = computed(() => {
    return form.items.reduce((acc, item) => acc + (Number(item.quantity || 0) * Number(item.unit_price || 0)), 0);
});

const totalAmount = computed(() => {
    const sub = subtotal.value;
    const disc = Number(form.discount_amount || 0);
    const tax = Number(form.tax_amount || 0);
    return Math.max(0, sub - disc + tax);
});

const submit = () => {
    form.post(route('invoices.store'));
};
</script>

<template>
    <Head title="إنشاء فاتورة أتعاب جديدة" />

    <TenantLayout>
        <template #title>
            <span>📝 إنشاء فاتورة أتعاب قانونية</span>
        </template>
        <template #actions>
            <Link :href="route('invoices.index')" class="px-4 py-2 rounded-xl text-sm font-bold text-stone-600 hover:bg-stone-100 transition-colors">
                إلغاء وعودة
            </Link>
        </template>

        <form @submit.prevent="submit" class="max-w-4xl mx-auto space-y-6">
            <!-- Basic Information Card -->
            <div class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-stone-800 border-b border-stone-100 pb-3">1. البيانات الأساسية للفاتورة</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">رقم الفاتورة *</label>
                        <input v-model="form.invoice_number" type="text" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm font-bold text-blue-700 focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">الموكل المستهدف *</label>
                        <select v-model="form.client_id" required class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">-- اختر الموكل --</option>
                            <option v-for="cl in clients" :key="cl.id" :value="cl.id">{{ cl.name }}</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">القضية المرتبطة (اختياري)</label>
                        <select v-model="form.case_id" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500">
                            <option value="">-- اختر القضية --</option>
                            <option v-for="c in filteredCases" :key="c.id" :value="c.id">{{ c.title }} ({{ c.case_number }})</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-bold text-stone-700 mb-1.5">تاريخ الاستحقاق النهائي</label>
                    <input v-model="form.due_date" type="date" class="w-full sm:w-1/3 px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm focus:ring-2 focus:ring-blue-500/20 focus:border-blue-500" />
                </div>
            </div>

            <!-- Line Items Card -->
            <div class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm space-y-4">
                <div class="flex justify-between items-center border-b border-stone-100 pb-3">
                    <h3 class="text-base font-bold text-stone-800">2. بنود الفاتورة والخدمات القانونية</h3>
                    <button type="button" @click="addItem" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors">
                        + إضافة بند جديد
                    </button>
                </div>

                <div class="space-y-3">
                    <div v-for="(item, idx) in form.items" :key="idx" class="flex flex-col sm:flex-row items-center gap-3 bg-stone-50 p-3.5 rounded-xl border border-stone-200">
                        <div class="flex-1 w-full">
                            <input v-model="item.description" type="text" required placeholder="وصف الخدمة أو البند القانوني..." class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-sm focus:ring-2 focus:ring-blue-500/20" />
                        </div>
                        <div class="w-full sm:w-28">
                            <input v-model.number="item.quantity" type="number" min="1" required placeholder="الكمية" class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-sm text-center" />
                        </div>
                        <div class="w-full sm:w-36">
                            <input v-model.number="item.unit_price" type="number" step="0.01" min="0" required placeholder="السعر" class="w-full px-3 py-2 rounded-lg border border-stone-200 bg-white text-sm text-left font-bold" />
                        </div>
                        <div class="w-full sm:w-32 text-left font-black text-stone-800 text-sm">
                            {{ (Number(item.quantity || 0) * Number(item.unit_price || 0)).toLocaleString() }} ر.س
                        </div>
                        <button type="button" @click="removeItem(idx)" class="p-2 text-stone-400 hover:text-rose-600">✕</button>
                    </div>
                </div>
            </div>

            <!-- Financial Calculation Summary -->
            <div class="bg-white rounded-2xl p-6 border border-stone-200/80 shadow-sm space-y-4">
                <h3 class="text-base font-bold text-stone-800 border-b border-stone-100 pb-3">3. المجموع والإجماليات</h3>

                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">مبلغ الخصم (ر.س)</label>
                        <input v-model.number="form.discount_amount" type="number" step="0.01" min="0" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm font-bold" />
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-stone-700 mb-1.5">مبلغ الضريبة (ر.س)</label>
                        <input v-model.number="form.tax_amount" type="number" step="0.01" min="0" class="w-full px-4 py-2.5 rounded-xl border border-stone-200 bg-stone-50 text-sm font-bold" />
                    </div>
                    <div class="bg-stone-50 p-4 rounded-xl border border-stone-200 text-left flex flex-col justify-center">
                        <span class="text-xs text-stone-500 font-bold block">المجموع النهائي الفعلي</span>
                        <span class="text-2xl font-black text-blue-700 mt-1">{{ totalAmount.toLocaleString() }} <span class="text-xs font-normal">ر.س</span></span>
                    </div>
                </div>

                <div class="flex justify-end gap-3 pt-4 border-t border-stone-100">
                    <button type="submit" :disabled="form.processing" class="px-8 py-3.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl shadow-lg shadow-blue-200 transition-all text-sm">
                        حفظ وإنشاء الفاتورة
                    </button>
                </div>
            </div>
        </form>
    </TenantLayout>
</template>
