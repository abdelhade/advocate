<?php

namespace Database\Seeders;

use App\Models\SubscriptionPlan;
use Illuminate\Database\Seeder;

class SubscriptionPlanSeeder extends Seeder
{
    public function run(): void
    {
        $plans = [
            [
                'name' => 'المجانية الشاملة',
                'slug' => 'free',
                'tagline' => 'مجاني بالكامل لكافة مكاتب المحاماة مدى الحياة',
                'max_users' => 999999,
                'max_clients' => 999999,
                'max_cases' => 999999,
                'max_storage_mb' => 999999,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 1,
                'feature_tasks' => true,
                'feature_billing' => true,
                'feature_documents' => true,
                'feature_portal' => true,
                'features' => [
                    'عدد غير محدود من المستخدمين والمحامين',
                    'عدد غير محدود من الموكلين',
                    'عدد غير محدود من القضايا والجلسات',
                    'إدارة وتوثيق القضايا الأساسية والجلسات',
                    'إدارة المهام والأعمال القضائية',
                    'إدارة الفوترة وأتعاب المحاماة وسندات القبض',
                    'إدارة المستندات والأرشيف الإلكتروني',
                    'بوابة الموكلين التفاعلية',
                ],
            ],
            [
                'name' => 'الاحترافية (مجانية)',
                'slug' => 'professional',
                'tagline' => 'مجاني بالكامل لكافة مكاتب المحاماة مدى الحياة',
                'max_users' => 999999,
                'max_clients' => 999999,
                'max_cases' => 999999,
                'max_storage_mb' => 999999,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 2,
                'feature_tasks' => true,
                'feature_billing' => true,
                'feature_documents' => true,
                'feature_portal' => true,
                'features' => [
                    'عدد غير محدود من المستخدمين والمحامين',
                    'عدد غير محدود من الموكلين',
                    'عدد غير محدود من القضايا والجلسات',
                    'إدارة وتوثيق القضايا الأساسية والجلسات',
                    'إدارة المهام والأعمال القضائية',
                    'إدارة الفوترة وأتعاب المحاماة وسندات القبض',
                    'إدارة المستندات والأرشيف الإلكتروني',
                    'بوابة الموكلين التفاعلية',
                ],
            ],
            [
                'name' => 'المؤسسات (مجانية)',
                'slug' => 'enterprise',
                'tagline' => 'مجاني بالكامل لكافة مكاتب المحاماة مدى الحياة',
                'max_users' => 999999,
                'max_clients' => 999999,
                'max_cases' => 999999,
                'max_storage_mb' => 999999,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 3,
                'feature_tasks' => true,
                'feature_billing' => true,
                'feature_documents' => true,
                'feature_portal' => true,
                'features' => [
                    'عدد غير محدود من المستخدمين والمحامين',
                    'عدد غير محدود من الموكلين',
                    'عدد غير محدود من القضايا والجلسات',
                    'إدارة وتوثيق القضايا الأساسية والجلسات',
                    'إدارة المهام والأعمال القضائية',
                    'إدارة الفوترة وأتعاب المحاماة وسندات القبض',
                    'إدارة المستندات والأرشيف الإلكتروني',
                    'بوابة الموكلين التفاعلية',
                ],
            ],
        ];

        foreach ($plans as $plan) {
            SubscriptionPlan::updateOrCreate(
                ['slug' => $plan['slug']],
                array_merge($plan, ['is_active' => true])
            );
        }
    }
}
