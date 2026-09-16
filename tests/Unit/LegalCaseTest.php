<?php

namespace Tests\Unit;

use App\Models\Client;
use App\Models\LegalCase;
use App\Models\Tenant;
use App\Models\User;
use App\Services\TenantContext;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class LegalCaseTest extends TestCase
{
    use RefreshDatabase;

    protected Tenant $tenant;
    protected Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        // Clear tenant context before each test to ensure clean state
        app(TenantContext::class)->clear();

        $this->tenant = Tenant::factory()->create();
        $this->client = Client::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'عميل تجريبي',
            'email' => 'client@example.com',
            'phone' => '0501234567',
        ]);
    }

    /**
     * Test selecting all legal cases using raw SQL query (SELECT * FROM legal_cases).
     */
    public function test_select_all_from_legal_cases_raw_query(): void
    {
        LegalCase::factory()->count(3)->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
        ]);

        $results = DB::select('SELECT * FROM legal_cases');

        $this->assertCount(3, $results);
        $this->assertObjectHasProperty('case_number', $results[0]);
        $this->assertObjectHasProperty('title', $results[0]);
        $this->assertObjectHasProperty('status', $results[0]);
    }

    /**
     * Test selecting all legal cases via Eloquent (LegalCase::select('*')->get()).
     */
    public function test_select_all_legal_cases_via_eloquent_select_all(): void
    {
        $case1 = LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'title' => 'قضية رقم 1',
            'status' => 'active',
        ]);

        $case2 = LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'title' => 'قضية رقم 2',
            'status' => 'closed',
        ]);

        $cases = LegalCase::select('*')->get();

        $this->assertCount(2, $cases);
        $this->assertTrue($cases->contains('id', $case1->id));
        $this->assertTrue($cases->contains('id', $case2->id));
        $this->assertEquals('قضية رقم 1', $cases->firstWhere('id', $case1->id)->title);
    }

    /**
     * Test select * from legal cases with active Tenant Context scoping.
     */
    public function test_select_all_legal_cases_scoped_by_tenant(): void
    {
        $tenantB = Tenant::factory()->create();
        $clientB = Client::create([
            'tenant_id' => $tenantB->id,
            'name' => 'عميل مكتب 2',
        ]);

        $caseTenantA = LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'title' => 'قضية المكتب الأول',
        ]);

        $caseTenantB = LegalCase::factory()->create([
            'tenant_id' => $tenantB->id,
            'client_id' => $clientB->id,
            'title' => 'قضية المكتب الثاني',
        ]);

        // Set Tenant Context to Tenant A
        app(TenantContext::class)->set($this->tenant);

        $cases = LegalCase::select('*')->get();

        $this->assertCount(1, $cases);
        $this->assertEquals($caseTenantA->id, $cases->first()->id);
        $this->assertEquals('قضية المكتب الأول', $cases->first()->title);

        // Switch Tenant Context to Tenant B
        app(TenantContext::class)->set($tenantB);

        $casesB = LegalCase::select('*')->get();

        $this->assertCount(1, $casesB);
        $this->assertEquals($caseTenantB->id, $casesB->first()->id);
        $this->assertEquals('قضية المكتب الثاني', $casesB->first()->title);
    }

    /**
     * Test selecting legal cases with eager-loaded relations (client and primary lawyer).
     */
    public function test_select_all_legal_cases_with_relations(): void
    {
        $lawyer = User::factory()->create(['name' => 'المحامي الأول']);

        $case = LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'primary_lawyer_id' => $lawyer->id,
            'title' => 'قضية تعويضات',
        ]);

        $cases = LegalCase::select('*')->with(['client', 'primaryLawyer'])->get();

        $this->assertCount(1, $cases);
        $selectedCase = $cases->first();
        $this->assertEquals('عميل تجريبي', $selectedCase->client->name);
        $this->assertEquals('المحامي الأول', $selectedCase->primaryLawyer->name);
    }

    /**
     * Test select * from legal cases excludes soft-deleted cases unless withTrashed is called.
     */
    public function test_select_all_legal_cases_respects_soft_deletes(): void
    {
        $activeCase = LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'title' => 'قضية قائمة',
        ]);

        $deletedCase = LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'title' => 'قضية محذوفة',
        ]);

        $deletedCase->delete();

        // Normal query select * from legal_cases (where deleted_at is null)
        $cases = LegalCase::select('*')->get();
        $this->assertCount(1, $cases);
        $this->assertEquals($activeCase->id, $cases->first()->id);

        // Query including trashed records
        $allCasesIncludingTrashed = LegalCase::withTrashed()->select('*')->get();
        $this->assertCount(2, $allCasesIncludingTrashed);
    }

    /**
     * Test select * from legal cases with status filter query.
     */
    public function test_select_legal_cases_filtered_by_status(): void
    {
        LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'status' => 'active',
        ]);

        LegalCase::factory()->create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $this->client->id,
            'status' => 'won',
        ]);

        $activeCases = LegalCase::select('*')->where('status', 'active')->get();
        $wonCases = LegalCase::select('*')->where('status', 'won')->get();

        $this->assertCount(1, $activeCases);
        $this->assertEquals('active', $activeCases->first()->status);

        $this->assertCount(1, $wonCases);
        $this->assertEquals('won', $wonCases->first()->status);
    }
}
