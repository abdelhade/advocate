<?php

namespace Tests\Feature\Tenant;

use App\Models\Client;
use App\Models\LegalCase;
use App\Models\Task;
use App\Models\Tenant;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TaskTest extends TestCase
{
    use RefreshDatabase;

    protected User $user;
    protected Tenant $tenant;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tenant = Tenant::factory()->create();
        $this->user = User::factory()->create();
        $this->user->tenants()->attach($this->tenant->id, ['is_owner' => true, 'status' => 'active']);
    }

    public function test_tasks_page_can_be_rendered(): void
    {
        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->get(route('tasks.index'));

        $response->assertOk();
    }

    public function test_user_can_create_task(): void
    {
        $client = Client::create([
            'tenant_id' => $this->tenant->id,
            'name' => 'موكل تجريبي',
        ]);

        $case = LegalCase::create([
            'tenant_id' => $this->tenant->id,
            'client_id' => $client->id,
            'case_number' => 'CASE-101',
            'title' => 'قضية تجارية',
            'status' => 'active',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->post(route('tasks.store'), [
                'title' => 'إعداد لائحة الدعوى',
                'description' => 'كتابة الأسانيد والشواهد',
                'case_id' => $case->id,
                'priority' => 'high',
                'due_date' => now()->addDays(3)->format('Y-m-d'),
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'tenant_id' => $this->tenant->id,
            'title' => 'إعداد لائحة الدعوى',
            'priority' => 'high',
        ]);
    }

    public function test_user_can_update_task_status(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'creator_id' => $this->user->id,
            'title' => 'متابعة المحكمة',
            'priority' => 'medium',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->patch(route('tasks.update-status', $task->id), [
                'status' => 'completed',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('tasks', [
            'id' => $task->id,
            'status' => 'completed',
        ]);
    }

    public function test_user_can_delete_task(): void
    {
        $task = Task::create([
            'tenant_id' => $this->tenant->id,
            'creator_id' => $this->user->id,
            'title' => 'مهمة حظر حذف',
            'priority' => 'low',
            'status' => 'pending',
        ]);

        $response = $this->actingAs($this->user)
            ->withSession(['current_tenant_id' => $this->tenant->id])
            ->delete(route('tasks.destroy', $task->id));

        $response->assertRedirect();
        $this->assertSoftDeleted('tasks', ['id' => $task->id]);
    }
}
