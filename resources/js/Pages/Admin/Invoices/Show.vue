<script setup>
import AdminLayout from '@/Layouts/AdminLayout.vue';
import { adminPaths } from '@/adminPaths';
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    invoice: Object,
});

const printInvoice = () => {
    window.print();
};

const formatCurrency = (val) => {
    return Number(val || 0).toLocaleString('ar-EG', { minimumFractionDigits: 2 }) + ' ج.م';
};
</script>

<template>
    <Head :title="`فاتورة اشتراك: ${invoice.invoice_number}`" />

    <AdminLayout>
        <template #title>عرض فاتورة الاشتراك الرسمية</template>

        <!-- Breadcrumb & Top Bar (Hidden on print) -->
        <div class="print:hidden flex items-center justify-between gap-4 mb-8">
            <div class="flex items-center gap-2 text-xs text-stone-400 font-bold">
                <Link :href="adminPaths.invoices" class="hover:text-red-700 transition">فواتير الاشتراكات</Link>
                <span>/</span>
                <span class="text-stone-800 font-mono">{{ invoice.invoice_number }}</span>
            </div>

            <div class="flex items-center gap-3">
                <button
                    @click="printInvoice"
                    class="px-5 py-2.5 bg-stone-900 hover:bg-black text-white text-xs font-bold rounded-xl shadow-md transition flex items-center gap-2"
                >
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                    <span>طباعة الفاتورة</span>
                </button>
                <Link
                    :href="adminPaths.invoices"
                    class="px-4 py-2.5 bg-stone-100 hover:bg-stone-200 text-stone-700 text-xs font-bold rounded-xl border border-stone-200 transition"
                >
                    رجوع للفيست
                </Link>
            </div>
        </div>

        <!-- Printable Invoice Container -->
        <div class="max-w-4xl mx-auto bg-white rounded-3xl border border-stone-200 shadow-xl p-8 md:p-12 print:shadow-none print:border-none print:p-0 print:m-0 print:max-w-none text-right dir-rtl">
            
            <!-- Top Header & Branding -->
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-6 pb-8 border-b-2 border-stone-100">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-red-600 to-red-800 flex items-center justify-center text-white shadow-md">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 6l3 1m0 0l-3 9a5.002 5.002 0 006.001 0M6 7l3 9M6 7l6-2m6 2l3-1m-3 1l-3 9a5.002 5.002 0 006.001 0M18 7l3 9m-3-9l-6-2m0-2v2m0 16V5m0 16H9m3 0h3"></path></svg>
                        </div>
                        <div>
                            <span class="text-xl font-black text-stone-900">منصة جلسات</span>
                            <span class="block text-[10px] text-stone-400 font-bold uppercase">Jalsateg SaaS Platform</span>
                        </div>
                    </div>
                    <p class="text-xs text-stone-500 font-semibold">نظام إدارة المحاماة والاستشارات القانونية الذكي</p>
                </div>

                <div class="text-left font-mono" dir="ltr">
                    <span
                        :class="[
                            'inline-block px-4 py-1.5 rounded-full text-xs font-black border mb-2 text-right dir-rtl',
                            invoice.status === 'paid' ? 'bg-emerald-50 text-emerald-700 border-emerald-300' :
                            invoice.status === 'pending' ? 'bg-amber-50 text-amber-700 border-amber-300' :
                            invoice.status === 'overdue' ? 'bg-red-50 text-red-700 border-red-300' :
                            'bg-stone-100 text-stone-600 border-stone-300'
                        ]"
                    >
                        {{ invoice.status_label }}
                    </span>
                    <h1 class="text-2xl font-black text-stone-900 tracking-wider">{{ invoice.invoice_number }}</h1>
                    <p class="text-xs text-stone-400 mt-1 font-sans">تاريخ الإصدار: {{ invoice.issued_at }}</p>
                </div>
            </div>

            <!-- Billing Details Grid -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-8 my-8 pb-8 border-b border-stone-100">
                <!-- Tenant Office Details -->
                <div class="bg-stone-50/70 rounded-2xl p-6 border border-stone-200/80">
                    <p class="text-xs font-black text-stone-400 uppercase tracking-widest mb-3">المفوتر إليه (المكتب / المستأجر)</p>
                    <h3 class="text-lg font-black text-stone-900 mb-2">{{ invoice.tenant_name }}</h3>
                    <div class="space-y-1.5 text-xs text-stone-600 font-semibold">
                        <p><strong class="text-stone-800">صاحب المكتب:</strong> {{ invoice.owner_name }}</p>
                        <p dir="ltr" class="text-right"><strong class="text-stone-800">البريد الإلكتروني:</strong> {{ invoice.tenant_email }}</p>
                        <p dir="ltr" class="text-right"><strong class="text-stone-800">الهاتف:</strong> {{ invoice.tenant_phone }}</p>
                    </div>
                </div>

                <!-- Invoice Meta Info -->
                <div class="bg-stone-50/70 rounded-2xl p-6 border border-stone-200/80 flex flex-col justify-between">
                    <div>
                        <p class="text-xs font-black text-stone-400 uppercase tracking-widest mb-3">تفاصيل الاستحقاق والدفع</p>
                        <div class="space-y-2 text-xs font-semibold text-stone-700">
                            <div class="flex justify-between">
                                <span class="text-stone-500">تاريخ الاستحقاق:</span>
                                <span class="font-mono font-bold">{{ invoice.due_date }}</span>
                            </div>
                            <div class="flex justify-between">
                                <span class="text-stone-500">طريقة الدفع:</span>
                                <span class="font-bold">{{ invoice.payment_method }}</span>
                            </div>
                            <div v-if="invoice.paid_at" class="flex justify-between">
                                <span class="text-stone-500">تاريخ السداد:</span>
                                <span class="font-mono font-bold text-emerald-700">{{ invoice.paid_at }}</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Items Table -->
            <div class="mb-8">
                <table class="w-full text-right border-collapse">
                    <thead>
                        <tr class="bg-stone-100 text-stone-600 text-xs font-bold border-b border-stone-200">
                            <th class="py-3.5 px-6 rounded-r-xl">الخدمة / خطة الاشتراك</th>
                            <th class="py-3.5 px-6">دورة الفوترة</th>
                            <th class="py-3.5 px-6 text-left rounded-l-xl">المبلغ</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100 text-sm">
                        <tr>
                            <td class="py-5 px-6 font-bold text-stone-800">
                                {{ invoice.plan_name }}
                                <span v-if="invoice.notes" class="block text-xs font-normal text-stone-500 mt-1">{{ invoice.notes }}</span>
                            </td>
                            <td class="py-5 px-6 text-xs font-semibold text-stone-600">
                                {{ invoice.billing_period }}
                            </td>
                            <td class="py-5 px-6 font-bold font-mono text-stone-900 text-left" dir="ltr">
                                {{ formatCurrency(invoice.amount) }}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- Totals Summary -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-end gap-6 pt-6 border-t border-stone-200">
                <div class="text-xs text-stone-400 space-y-1">
                    <p class="font-bold text-stone-600">ملاحظات وتعليمات:</p>
                    <p>تم إصدار هذه الفاتورة إلكترونياً وهي موثقة بسجلات منصة جلسات.</p>
                </div>

                <div class="w-full sm:w-72 space-y-2 text-xs font-semibold text-stone-700 bg-stone-50 p-5 rounded-2xl border border-stone-200">
                    <div class="flex justify-between">
                        <span class="text-stone-500">المبلغ الأساسي:</span>
                        <span class="font-mono font-bold">{{ formatCurrency(invoice.amount) }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-stone-500">الضريبة المضافة:</span>
                        <span class="font-mono font-bold">{{ formatCurrency(invoice.tax_amount) }}</span>
                    </div>
                    <div class="flex justify-between pt-2 border-t border-stone-200 text-sm font-black text-stone-900">
                        <span>الإجمالي المطلوب:</span>
                        <span class="font-mono text-red-700 text-base">{{ formatCurrency(invoice.total_amount) }}</span>
                    </div>
                </div>
            </div>

            <!-- Printable Footer -->
            <div class="mt-12 pt-6 border-t border-stone-100 text-center text-[10px] text-stone-400 font-semibold">
                شكراً لاختياركم منصة جلسات — الدعم الفني والمساعدة: support@jalsateg.com
            </div>
        </div>
    </AdminLayout>
</template>

<style>
@media print {
    body {
        background: white !important;
    }
    aside, header, nav, .print\:hidden {
        display: none !important;
    }
    main {
        padding: 0 !important;
        margin: 0 !important;
    }
}
</style>
