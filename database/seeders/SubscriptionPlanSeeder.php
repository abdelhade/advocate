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
                'max_storage_mb' => 512,
                'price_monthly' => 0,
                'price_yearly' => 0,
                'sort_order' => 1,
                'features' => [
                    '1 مستخدم (محامي واحد)',
                    '10 موكلين كحد أقصى',
                    'إدارة وتوثيق القضايا الأساسية',
                ],
            ],
            [
                'name' => 'الاحترافية',
                'slug' => 'professional',
                'tagline' => 'المثالية لمكاتب المحاماة المتنامية',
                'max_users' => 5,
                'max_storage_mb' => 5120,
                'price_monthly' => 650,
                'price_yearly' => 5000,
                'sort_order' => 2,
                'features' => [
                    '5 مستخدمين (فريق عمل)',
                    '100 موكل',
                    'إدارة وتوثيق القضايا والجلسات',
                    'إدارة الفوترة وأقساط الأتعاب',
                    'تسجيل وتتبع المصروفات والمالية',
                ],
            ],
            [
                'name' => 'المؤسسات',
                'slug' => 'enterprise',
                'tagline' => 'لمكاتب وشركات المحاماة الكبيرة',
                'max_users' => 25,
                'max_storage_mb' => 20480,
                'price_monthly' => 1250,
                'price_yearly' => 9000,
                'sort_order' => 3,
                'features' => [
                    '25 مستخدم (فريق كامل)',
                    '1,000 موكل',
                    'إدارة وتوثيق القضايا والجلسات',
                    'إدارة الفوترة وأقساط الأتعاب',
                    'تسجيل وتتبع المصروفات والمالية',
                    'دعم فني 24/7 متواصل مخصص',
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
