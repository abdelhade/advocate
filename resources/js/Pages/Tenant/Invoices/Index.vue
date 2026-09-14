<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head, Link, router } from '@inertiajs/vue3';

const props = defineProps({
    invoices: Object,
    stats: Object,
    filters: Object,
});

const statusLabels = {
    draft: { text: 'مسودة', class: 'bg-stone-100 text-stone-600 border-stone-200' },
    posted: { text: 'بانتظار السداد', class: 'bg-amber-50 text-amber-700 border-amber-200' },
    partially_paid: { text: 'مدفوعة جزئياً', class: 'bg-blue-50 text-blue-700 border-blue-200' },
    paid: { text: 'مدفوعة بالكامل', class: 'bg-emerald-50 text-emerald-700 border-emerald-200' },
    voided: { text: 'ملغاة', class: 'bg-rose-50 text-rose-700 border-rose-200' },
};

const deleteInvoice = (invoice) => {
    if (confirm(`هل أنت متأكد من حذف الفاتورة رقم ${invoice.invoice_number}؟`)) {
        router.delete(route('invoices.destroy', invoice.id));
    }
};
</script>

<template>
    <Head title="الفواتير والمطالبات المالية" />

    <TenantLayout>
        <template #title>
            <span>📜 الفواتير والمطالبات المالية</span>
        </template>
        <template #actions>
            <Link
                :href="route('invoices.create')"
                class="px-5 py-2.5 bg-blue-700 hover:bg-blue-800 text-white font-bold rounded-xl shadow-md shadow-blue-200 transition-all flex items-center gap-2 text-sm"
            >
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                إنشاء فاتورة جديدة
            </Link>
        </template>

        <!-- Summary Cards -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 mb-8">
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-700 flex items-center justify-center text-xl font-bold">💳</div>
                <div>
                    <p class="text-xs font-bold text-stone-400">إجمالي الفواتير الصادرة</p>
                    <p class="text-xl font-black text-stone-800 mt-1">{{ Number(stats.total_invoiced).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-700 flex items-center justify-center text-xl font-bold">✅</div>
                <div>
                    <p class="text-xs font-bold text-stone-400">المبالغ المحصّلة</p>
                    <p class="text-xl font-black text-emerald-700 mt-1">{{ Number(stats.total_paid).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-700 flex items-center justify-center text-xl font-bold">⏳</div>
                <div>
                    <p class="text-xs font-bold text-stone-400">المتبقي غير المحصل</p>
                    <p class="text-xl font-black text-amber-700 mt-1">{{ Number(stats.total_unpaid).toLocaleString() }} <span class="text-xs font-normal"></span></p>
                </div>
            </div>
            <div class="bg-white rounded-2xl p-5 border border-stone-200/80 shadow-sm flex items-center gap-4">
                <div class="w-12 h-12 rounded-xl bg-stone-100 text-stone-700 flex items-center justify-center text-xl font-bold">📄</div>
                <div>
                    <p class="text-xs font-bold text-stone-400">عدد الفواتير</p>
                    <p class="text-xl font-black text-stone-800 mt-1">{{ stats.invoices_count }}</p>
                </div>
            </div>
        </div>

        <!-- Invoices Table -->
        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-stone-700">
                    <thead class="bg-stone-50 text-xs font-bold text-stone-500 border-b border-stone-200">
                        <tr>
                            <th class="px-6 py-4">رقم الفاتورة</th>
                            <th class="px-6 py-4">الموكل</th>
                            <th class="px-6 py-4">القضية</th>
                            <th class="px-6 py-4">الإجمالي</th>
                            <th class="px-6 py-4">المسدد</th>
                            <th class="px-6 py-4">الحالة</th>
                            <th class="px-6 py-4">تاريخ الاستحقاق</th>
                            <th class="px-6 py-4 text-center">الإجراءات</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="inv in invoices.data" :key="inv.id" class="hover:bg-stone-50/80 transition-colors">
                            <td class="px-6 py-4 font-black text-blue-700">
                                <Link :href="route('invoices.show', inv.id)" class="hover:underline">{{ inv.invoice_number }}</Link>
                            </td>
                            <td class="px-6 py-4 font-bold text-stone-800">{{ inv.client?.name }}</td>
                            <td class="px-6 py-4 text-stone-500">{{ inv.case ? `${inv.case.title} (${inv.case.case_number})` : '-' }}</td>
                            <td class="px-6 py-4 font-black text-stone-800">{{ Number(inv.total_amount).toLocaleString() }} </td>
                            <td class="px-6 py-4 font-bold text-emerald-700">{{ Number(inv.paid_amount).toLocaleString() }} </td>
                            <td class="px-6 py-4">
                                <span :class="['px-3 py-1 text-xs font-bold rounded-lg border', statusLabels[inv.status]?.class]">
                                    {{ statusLabels[inv.status]?.text }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs text-stone-500">{{ inv.due_date ? new Date(inv.due_date).toLocaleDateString('ar-EG') : '-' }}</td>
                            <td class="px-6 py-4 text-center">
                                <div class="flex justify-center gap-2">
                                    <Link :href="route('invoices.show', inv.id)" class="px-3 py-1.5 bg-blue-50 text-blue-700 hover:bg-blue-100 rounded-lg text-xs font-bold transition-colors">
                                        عرض/طباعة
                                    </Link>
                                    <button @click="deleteInvoice(inv)" class="p-1.5 text-stone-400 hover:text-rose-600 rounded-lg hover:bg-rose-50 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path></svg>
                                    </button>
                                </div>
                            </td>
                        </tr>
                        <tr v-if="!invoices.data || invoices.data.length === 0">
                            <td colspan="8" class="p-12 text-center text-stone-400">
                                <p class="font-bold text-stone-600">لا توجد فواتير مسجلة</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </TenantLayout>
</template>
