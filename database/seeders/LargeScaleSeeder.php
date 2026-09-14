<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class LargeScaleSeeder extends Seeder
{
    /**
     * Seed 10,000 tenants, each with 100 clients, each client with 100 cases and 100 invoices.
     */
    public function run(): void
    {
        ini_set('memory_limit', '-1');
        set_time_limit(0);

        DB::disableQueryLog();
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        $totalTenants = 10000;
        $clientsPerTenant = 100;
        $casesPerClient = 100;
        $invoicesPerClient = 100;

        $now = now()->toDateTimeString();
        $defaultPassword = Hash::make('00000000');

        $caseTypes = ['تجاري', 'عمالي', 'مدني', 'جنائي', 'أحوال شخصية'];
        $courts = ['المحكمة العامة', 'المحكمة التجارية', 'المحكمة العمالية', 'محكمة التنفيذ'];
        $caseStatuses = ['active', 'suspended', 'won', 'lost', 'closed'];
        $invoiceStatuses = ['posted', 'draft', 'partially_paid'];

        if ($this->command) {
            $this->command->info("بدء إنشاء {$totalTenants} مكتب محاماة (كل مكتب: 100 موكل، 100 قضية/موكل، 100 فاتورة/موكل)...");
        }

        $caseCounter = 1;
        $invoiceCounter = 1;

        for ($t = 1; $t <= $totalTenants; $t++) {
            $tenantId = (string) Str::uuid();
            $officeSlug = 'office-' . $t . '-' . Str::random(5);

            $userEmail = "office{$t}_" . Str::random(4) . "@example.com";

            // 1. Insert Tenant
            DB::table('tenants')->insert([
                'id' => $tenantId,
                'name' => "مكتب المحاماة رقم {$t}",
                'slug' => $officeSlug,
                'email' => $userEmail,
                'phone' => '05' . rand(10000008, 99999999),
                'status' => 'active',
                'settings' => json_encode(['currency' => 'SAR', 'retention_days' => 365]),
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 1b. Create Owner User for Tenant
            $userId = DB::table('users')->insertGetId([
                'name' => "المحامي المسؤول - مكتب {$t}",
                'email' => $userEmail,
                'password' => $defaultPassword,
                'phone' => '05' . rand(10000008, 99999999),
                'status' => 'active',
                'is_super_admin' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            DB::table('tenant_user')->insert([
                'tenant_id' => $tenantId,
                'user_id' => $userId,
                'is_owner' => true,
                'status' => 'active',
                'joined_at' => $now,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            // 2. Insert Clients
            $clientsData = [];
            for ($c = 1; $c <= $clientsPerTenant; $c++) {
                $clientsData[] = [
                    'tenant_id' => $tenantId,
                    'type' => $c % 3 === 0 ? 'company' : ($c % 5 === 0 ? 'organization' : 'individual'),
                    'name' => "الموكل {$c} - مكتب {$t}",
                    'national_id_or_cr' => '10' . rand(10000000, 99999999),
                    'email' => "client{$t}_{$c}@example.com",
                    'phone' => '05' . rand(10000000, 99999999),
                    'address' => 'الرياض، المملكة العربية السعودية',
                    'notes' => 'بيانات موكل تجريبية',
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('clients')->insert($clientsData);
            unset($clientsData);

            $clientIds = DB::table('clients')
                ->where('tenant_id', $tenantId)
                ->pluck('id');

            // 3. Insert Legal Cases and Invoices
            $casesBatch = [];
            $invoicesBatch = [];

            foreach ($clientIds as $clientId) {
                // 100 cases per client
                for ($cs = 1; $cs <= $casesPerClient; $cs++) {
                    $casesBatch[] = [
                        'tenant_id' => $tenantId,
                        'client_id' => $clientId,
                        'case_number' => "CAS-T{$t}-C{$clientId}-{$cs}-" . ($caseCounter++),
                        'internal_number' => "INT-{$cs}",
                        'title' => 'قضية ' . $caseTypes[$cs % 5] . " للموكل رقم {$clientId}",
                        'court_name' => $courts[$cs % 4],
                        'circuit' => 'الدائرة ' . (($cs % 20) + 1),
                        'case_type' => $caseTypes[$cs % 5],
                        'status' => $caseStatuses[$cs % 5],
                        'primary_lawyer_id' => null,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (count($casesBatch) >= 500) {
                        DB::table('legal_cases')->insert($casesBatch);
                        $casesBatch = [];
                    }
                }

                // 100 invoices per client
                for ($inv = 1; $inv <= $invoicesPerClient; $inv++) {
                    $subtotal = rand(1000, 50000);
                    $tax = round($subtotal * 0.15, 2);
                    $total = $subtotal + $tax;

                    $invoicesBatch[] = [
                        'tenant_id' => $tenantId,
                        'client_id' => $clientId,
                        'case_id' => null,
                        'invoice_number' => "INV-T{$t}-C{$clientId}-{$inv}-" . ($invoiceCounter++),
                        'status' => $invoiceStatuses[$inv % 3],
                        'subtotal' => $subtotal,
                        'discount_amount' => 0.00,
                        'tax_amount' => $tax,
                        'total_amount' => $total,
                        'paid_amount' => 0.00,
                        'due_date' => date('Y-m-d', strtotime('+' . rand(1, 60) . ' days')),
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];

                    if (count($invoicesBatch) >= 500) {
                        DB::table('invoices')->insert($invoicesBatch);
                        $invoicesBatch = [];
                    }
                }
            }

            if (!empty($casesBatch)) {
                DB::table('legal_cases')->insert($casesBatch);
                $casesBatch = [];
            }
            if (!empty($invoicesBatch)) {
                DB::table('invoices')->insert($invoicesBatch);
                $invoicesBatch = [];
            }

            unset($clientIds);

            if ($t % 10 === 0) {
                gc_collect_cycles();
            }

            if ($this->command && ($t % 10 === 0 || $t === $totalTenants)) {
                $this->command->info("تم إنجاز {$t} مكتب من أصل {$totalTenants}...");
            }
        }

        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        if ($this->command) {
            $this->command->info("اكتمل إنشاء {$totalTenants} مكتب بنجاح!");
        }
    }
}
