<script setup>
import TenantLayout from '@/Layouts/TenantLayout.vue';
import { Head } from '@inertiajs/vue3';

const props = defineProps({
    logs: Object,
    filters: Object,
});
</script>

<template>
    <Head title="سجل النشاطات والتغيرات" />

    <TenantLayout>
        <template #title>
            <span>🛡️ سجل نشاطات وأعمال المكتب</span>
        </template>

        <div class="bg-white rounded-2xl border border-stone-200/80 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-right text-sm text-stone-700">
                    <thead class="bg-stone-50 text-xs font-bold text-stone-500 border-b border-stone-200">
                        <tr>
                            <th class="px-6 py-4">المستخدم</th>
                            <th class="px-6 py-4">الإجراء</th>
                            <th class="px-6 py-4">الكيان المستهدف</th>
                            <th class="px-6 py-4">عنوان IP</th>
                            <th class="px-6 py-4">التاريخ والوقت</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-stone-100">
                        <tr v-for="log in logs.data" :key="log.id" class="hover:bg-stone-50/80 transition-colors">
                            <td class="px-6 py-4 font-bold text-stone-800">
                                <div>{{ log.user?.name || 'النظام' }}</div>
                                <div class="text-[11px] text-stone-400 font-normal dir-ltr text-right">{{ log.user?.email }}</div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-bold rounded-lg border bg-blue-50 text-blue-700 border-blue-200 uppercase">
                                    {{ log.action }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-xs font-bold text-stone-700">
                                {{ log.entity_type }} (ID: {{ log.entity_id }})
                            </td>
                            <td class="px-6 py-4 text-xs text-stone-500 font-mono">{{ log.ip_address || '-' }}</td>
                            <td class="px-6 py-4 text-xs text-stone-500">{{ new Date(log.created_at).toLocaleString('ar-EG') }}</td>
                        </tr>
                        <tr v-if="!logs.data || logs.data.length === 0">
                            <td colspan="5" class="p-12 text-center text-stone-400">
                                <p class="font-bold text-stone-600">لا توجد نشاطات مسجلة في السجل</p>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </div>
    </TenantLayout>
</template>
