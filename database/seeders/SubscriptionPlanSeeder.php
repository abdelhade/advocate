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
                'name' => 'المجانية',
                'slug' => 'free',
                'tagline' => 'للمحامين المبتدئين أو التجربة الأولوية',
                'max_users' => 1,
                'max_clients' => 10,
                'max_cases' => 100,
                'max_storage_mb' => 512,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 1,
                'feature_tasks' => false,
                'feature_billing' => false,
                'feature_documents' => false,
                'feature_portal' => false,
                'features' => [
                    '1 مستخدم (محامي واحد)',
                    '10 موكلين كحد أقصى',
                    '100 قضية كحد أقصى',
                    'إدارة وتوثيق القضايا الأساسية',
                ],
            ],
            [
                'name' => 'الاحترافية',
                'slug' => 'professional',
                'tagline' => 'المثالية لمكاتب المحاماة المتنامية',
                'max_users' => 10,
                'max_clients' => 200,
                'max_cases' => 3000,
                'max_storage_mb' => 5120,
                'price_monthly' => 156,
                'price_yearly' => 1560,
                'sort_order' => 2,
                'feature_tasks' => true,
                'feature_billing' => true,
                'feature_documents' => true,
                'feature_portal' => true,
                'features' => [
                    '10 مستخدمين (فريق عمل)',
                    '200 موكل',
                    '3,000 قضية',
                    'إدارة وتوثيق القضايا والجلسات',
                    'إدارة المهام',
                    'إدارة الفوترة وأقساط الأتعاب',
                    'إدارة المستندات والتوثيق',
                    'بوابة الموكلين',
                ],
            ],
            [
                'name' => 'المؤسسات',
                'slug' => 'enterprise',
                'tagline' => 'لمكاتب وشركات المحاماة الكبيرة',
                'max_users' => 50,
                'max_clients' => 1000,
                'max_cases' => 20000,
                'max_storage_mb' => 20480,
                'price_monthly' => 416,
                'price_yearly' => 4160,
                'sort_order' => 3,
                'feature_tasks' => true,
                'feature_billing' => true,
                'feature_documents' => true,
                'feature_portal' => true,
                'features' => [
                    '50 مستخدم (فريق كامل)',
                    '1,000 موكل',
                    '20,000 قضية',
                    'إدارة وتوثيق القضايا والجلسات',
                    'إدارة المهام',
                    'إدارة الفوترة وأقساط الأتعاب',
                    'إدارة المستندات والتوثيق',
                    'بوابة الموكلين',
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
