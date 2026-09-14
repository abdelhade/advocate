<?php

namespace Database\Seeders;

use App\Models\Client;
use App\Models\LegalCase;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Central Super Admin
        $admin = User::create([
            'name' => 'مدير النظام الرئيسي',
            'email' => 'admin@advocate.com',
            'password' => Hash::make('password'),
            'phone' => '01000000000',
            'status' => 'active',
            'is_super_admin' => true,
        ]);

        // 2. Create Demo Lawyer / Tenant Owner
        $lawyer = User::create([
            'name' => 'المحامي أحمد علي',
            'email' => 'ahmed@law.com',
            'password' => Hash::make('password'),
            'phone' => '01111111111',
            'status' => 'active',
            'is_super_admin' => false,
        ]);

        // 3. Create Demo Tenant Office 1
        $tenant1 = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'مكتب المحامي أحمد علي - القاهرة',
            'slug' => 'cairo-office',
            'email' => 'cairo@law.com',
            'phone' => '0222222222',
            'status' => 'active',
            'settings' => ['currency' => 'EGP'],
        ]);

        // Attach Lawyer to Tenant 1 as Owner
        $tenant1->users()->attach($lawyer->id, [
            'is_owner' => true,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // 4. Create Demo Tenant Office 2 (Demonstrating Multi-Tenant User Membership!)
        $tenant2 = Tenant::create([
            'id' => (string) Str::uuid(),
            'name' => 'مكتب المستشار أحمد - الإسكندرية',
            'slug' => 'alex-office',
            'email' => 'alex@law.com',
            'phone' => '0333333333',
            'status' => 'active',
            'settings' => ['currency' => 'EGP'],
        ]);

        // Attach same Lawyer to Tenant 2 as Consultant
        $tenant2->users()->attach($lawyer->id, [
            'is_owner' => false,
            'status' => 'active',
            'joined_at' => now(),
        ]);

        // 5. Seed Demo Operational Data inside Tenant 1 Context
        app(TenantContext::class)->set($tenant1);

        $client = Client::create([
            'tenant_id' => $tenant1->id,
            'type' => 'company',
            'name' => 'شركة الأمل للتجارة والاستيراد',
            'national_id_or_cr' => '1029384756',
            'email' => 'info@alamal.com',
            'phone' => '01234567890',
            'address' => 'القاهرة - مدينة نصر',
        ]);

        LegalCase::create([
            'tenant_id' => $tenant1->id,
            'client_id' => $client->id,
            'case_number' => '1024/2026',
            'internal_number' => 'CAS-001',
            'title' => 'نزاع تجاري حول عقد توريد بضائع',
            'court_name' => 'محكمة القاهرة الاقتصادية',
            'circuit' => 'الدائرة الأولى تجاري',
            'case_type' => 'تجاري',
            'status' => 'active',
            'primary_lawyer_id' => $lawyer->id,
        ]);
    }
}
