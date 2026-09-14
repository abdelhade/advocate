<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoice: Object,
    tenant: Object,
});

const printInvoice = () => {
    window.print();
};

const statusLabels = {
    draft: { text: 'مسودة', class: 'bg-stone-100 text-stone-600 border-stone-200' },
    posted: { text: 'بانتظار السداد', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    partially_paid: { text: 'مدفوعة جزئياً', class: 'bg-blue-50 text-blue-700 border-blue-200' },
    paid: { text: 'مدفوعة بالكامل', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    voided: { text: 'ملغاة', class: 'bg-rose-50 text-rose-700 border-rose-200' },
};
</script>

<template>
    <Head :title="`فاتورة رقم ${invoice.invoice_number}`" />

    <TenantLayout>
        <template #title>
            <span>📜 تفاصيل الفاتورة #{{ invoice.invoice_number }}</span>
        </template>
        <template #actions>
            <button
                @click="printInvoice"
                class="px-5 py-2.5 bg-stone-800 hover:bg-stone-900 text-white font-bold rounded-xl shadow-md transition-all flex items-center gap-2 text-sm print:hidden"
            >
                🖨️ طباعة الفاتورة
            </button>
            <Link :href="route('invoices.index')" class="px-4 py-2 rounded-xl text-sm font-bold text-stone-600 hover:bg-stone-100 transition-colors print:hidden">
                رجوع للقائمة
            </Link>
        </template>

        <!-- Printable Invoice Container -->
        <div class="max-w-3xl mx-auto bg-white rounded-2xl border border-stone-200 p-8 sm:p-12 shadow-lg print:shadow-none print:border-none print:p-0">
            <!-- Header -->
            <div class="flex justify-between items-start border-b border-stone-200 pb-8 mb-8">
                <div class="space-y-1">
                    <h1 class="text-2xl font-black text-blue-700">{{ tenant.name || 'مكتب المحاماة' }}</h1>
                    <p class="text-xs text-stone-500 font-bold">فاتورة مطالعة أتعاب وخدمات قانونية</p>
                </div>
                <div class="text-left space-y-1">
                    <span :class="['inline-block px-3 py-1 text-xs font-bold rounded-lg border mb-2', statusLabels[invoice.status]?.class]">
                        {{ statusLabels[invoice.status]?.text }}
                    </span>
                    <p class="text-sm font-black text-stone-800">رقم الفاتورة: {{ invoice.invoice_number }}</p>
                    <p class="text-xs text-stone-500">تاريخ الإصدار: {{ new Date(invoice.created_at).toLocaleDateString('ar-EG') }}</p>
                    <p v-if="invoice.due_date" class="text-xs text-stone-500">تاريخ الاستحقاق: {{ new Date(invoice.due_date).toLocaleDateString('ar-EG') }}</p>
                </div>
            </div>

            <!-- Client & Case Information -->
            <div class="grid grid-cols-2 gap-6 bg-stone-50 p-6 rounded-2xl border border-stone-200/80 mb-8">
                <div>
                    <p class="text-xs font-bold text-stone-400 mb-1">المطلوب من السيد / الجهة:</p>
                    <p class="text-base font-black text-stone-800">{{ invoice.client?.name }}</p>
                    <p v-if="invoice.client?.phone" class="text-xs text-stone-500 dir-ltr text-right mt-0.5">📱 {{ invoice.client.phone }}</p>
                </div>
                <div v-if="invoice.case">
                    <p class="text-xs font-bold text-stone-400 mb-1">القضية المتعلقة:</p>
                    <p class="text-base font-black text-blue-700">{{ invoice.case.title }}</p>
                    <p class="text-xs text-stone-500 mt-0.5">رقم القضية: {{ invoice.case.case_number }}</p>
                </div>
            </div>

            <!-- Items Table -->
            <table class="w-full text-right text-sm text-stone-700 mb-8">
                <thead class="bg-stone-100 text-xs font-bold text-stone-600 border-b border-stone-200">
                    <tr>
                        <th class="px-4 py-3">#</th>
                        <th class="px-4 py-3">وصف البند / الخدمة</th>
                        <th class="px-4 py-3 text-center">الكمية</th>
                        <th class="px-4 py-3 text-left">سعر الوحدة</th>
                        <th class="px-4 py-3 text-left">الإجمالي</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-stone-100 border-b border-stone-200">
                    <tr v-for="(item, index) in invoice.items" :key="item.id">
                        <td class="px-4 py-3 text-stone-400 font-bold">{{ index + 1 }}</td>
                        <td class="px-4 py-3 font-bold text-stone-800">{{ item.description }}</td>
                        <td class="px-4 py-3 text-center font-bold">{{ item.quantity }}</td>
                        <td class="px-4 py-3 text-left font-bold">{{ Number(item.unit_price).toLocaleString() }} </td>
                        <td class="px-4 py-3 text-left font-black text-stone-900">{{ Number(item.total_price).toLocaleString() }} </td>
                    </tr>
                </tbody>
            </table>

            <!-- Totals & Payment Balance -->
            <div class="flex justify-end mb-8">
                <div class="w-72 space-y-2 text-xs font-bold text-stone-600">
                    <div class="flex justify-between py-1 border-b border-stone-100">
                        <span>المجموع الفرعي:</span>
                        <span class="text-stone-800 font-black">{{ Number(invoice.subtotal).toLocaleString() }} </span>
                    </div>
                    <div v-if="Number(invoice.discount_amount) > 0" class="flex justify-between py-1 border-b border-stone-100 text-rose-600">
                        <span>الخصم:</span>
                        <span>- {{ Number(invoice.discount_amount).toLocaleString() }} </span>
                    </div>
                    <div v-if="Number(invoice.tax_amount) > 0" class="flex justify-between py-1 border-b border-stone-100">
                        <span>الضريبة:</span>
                        <span class="text-stone-800 font-black">+ {{ Number(invoice.tax_amount).toLocaleString() }} </span>
                    </div>
                    <div class="flex justify-between py-2 text-base font-black text-blue-700 border-b-2 border-blue-700">
                        <span>إجمالي الفاتورة:</span>
                        <span>{{ Number(invoice.total_amount).toLocaleString() }} </span>
                    </div>
                    <div class="flex justify-between py-1 text-emerald-700 font-bold">
                        <span>المبلغ المسدد:</span>
                        <span>{{ Number(invoice.paid_amount).toLocaleString() }} </span>
                    </div>
                    <div class="flex justify-between py-1 text-amber-700 font-black text-sm">
                        <span>المتبقي للسداد:</span>
                        <span>{{ (Number(invoice.total_amount) - Number(invoice.paid_amount)).toLocaleString() }} </span>
                    </div>
                </div>
            </div>

            <!-- Payment History section if any -->
            <div v-if="invoice.payments && invoice.payments.length > 0" class="mt-8 border-t border-stone-200 pt-6">
                <h4 class="text-xs font-bold text-stone-400 uppercase tracking-widest mb-3">سجل المقبوضات على هذه الفاتورة</h4>
                <div class="space-y-2">
                    <div v-for="p in invoice.payments" :key="p.id" class="flex justify-between items-center bg-stone-50 px-4 py-2.5 rounded-xl border border-stone-200 text-xs">
                        <span class="font-bold text-stone-800">سند قبض رقم: {{ p.payment_number }} ({{ p.payment_method }})</span>
                        <span class="text-stone-500">{{ new Date(p.payment_date).toLocaleDateString('ar-EG') }}</span>
                        <span class="font-black text-emerald-700">+ {{ Number(p.amount).toLocaleString() }} </span>
                    </div>
                </div>
            </div>

            <!-- Footer Signature -->
            <div class="mt-12 pt-8 border-t border-stone-100 flex justify-between text-xs text-stone-400">
                <div>
                    <p class="font-bold text-stone-700">ختم ومصادقة المكتب</p>
                    <div class="h-16"></div>
                </div>
                <div class="text-left">
                    <p class="font-bold text-stone-700">توقيع المستلم</p>
                    <div class="h-16"></div>
                </div>
            </div>
        </div>
    </TenantLayout>
</template>
