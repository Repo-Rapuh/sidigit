<?php

namespace Tests\Feature\Orders;

use App\Livewire\Admin\Orders\Table as OrdersTable;
use App\Models\Branch;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Gate;
use Livewire\Livewire;
use Tests\TestCase;

class CancelledOrderLockingTest extends TestCase
{
    use RefreshDatabase;

    public function test_cancelled_order_form_is_read_only(): void
    {
        $user = $this->createUser();
        $order = $this->createOrder('dibatalkan');
        Gate::define('order.edit', fn (User $authUser) => $authUser->id === $user->id);
        Gate::define('workflow.override.locked-order', fn () => false);

        $response = $this
            ->actingAs($user)
            ->get(route('orders.edit', ['order' => $order->id]));

        $response->assertOk();
        $response->assertSeeText('read-only');
        $response->assertSeeText('Ubah status melalui aksi');
    }

    public function test_cancelled_order_non_status_fields_are_ignored_for_non_override_user(): void
    {
        $user = $this->createUser();
        $order = $this->createOrder('dibatalkan', 'catatan-lama');
        Gate::define('workflow.override.locked-order', fn () => false);

        $this->actingAs($user);
        app(OrderService::class)->update($order->id, [
            'status' => 'dibatalkan',
            'notes' => 'catatan-baru-harus-diabaikan',
            'items' => [],
            'payments' => [],
        ]);

        $order->refresh();

        $this->assertSame('dibatalkan', (string) $order->status);
        $this->assertSame('catatan-lama', (string) $order->notes);
    }

    public function test_status_change_modal_offers_cancelled_status(): void
    {
        $user = $this->createUser();
        $this->actingAs($user);

        $component = Livewire::test(OrdersTable::class);
        $options = collect($component->get('statusOptions'))->pluck('value')->all();

        $this->assertContains('dibatalkan', $options);
    }

    protected function createUser(): User
    {
        $branch = Branch::query()->updateOrCreate(
            ['id' => 1],
            [
                'name' => 'Headquarter',
                'is_main' => true,
            ]
        );

        $user = User::query()->create([
            'name' => 'Test User',
            'username' => 'test-user',
            'email' => 'test-user@example.com',
            'password' => 'password',
            'branch_id' => $branch->id,
        ]);

        $user->branches()->syncWithoutDetaching([$branch->id]);

        return $user;
    }

    protected function createOrder(string $status, string $notes = 'catatan-lama'): Order
    {
        return Order::query()->create([
            'order_no' => 'ORD-TEST-' . now()->format('YmdHisv'),
            'branch_id' => 1,
            'status' => $status,
            'order_date' => now()->toDateString(),
            'notes' => $notes,
        ]);
    }
}
