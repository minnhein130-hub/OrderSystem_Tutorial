<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\User;
use App\Services\OrderService;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderOwnershipTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_can_view_update_complete_and_cancel_own_pending_orders(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem();
        $orderToComplete = $this->createOrder($user, $item);
        $orderToCancel = $this->createOrder($user, $item);
        $line = $orderToComplete->orderItems->firstOrFail();

        $this->actingAs($user)
            ->get(route('orders.show', $orderToComplete))
            ->assertOk();
        $this->get(route('orders.edit', $orderToComplete))->assertOk();
        $this->put(route('orders.update', $orderToComplete), [
            'items' => [[
                'order_item_id' => $line->id,
                'item_id' => $item->id,
                'quantity' => 2,
            ]],
        ])->assertRedirect(route('orders.show', $orderToComplete))
            ->assertSessionHas('success', 'Order updated successfully.');

        $this->patch(route('orders.complete', $orderToComplete))
            ->assertRedirect(route('orders.show', $orderToComplete))
            ->assertSessionHas('success', 'Order completed successfully.');
        $this->patch(route('orders.cancel', $orderToCancel))
            ->assertRedirect(route('orders.show', $orderToCancel))
            ->assertSessionHas('success', 'Order cancelled successfully.');

        $this->assertDatabaseHas('orders', ['id' => $orderToComplete->id, 'user_id' => $user->id, 'status' => 'completed']);
        $this->assertDatabaseHas('orders', ['id' => $orderToCancel->id, 'user_id' => $user->id, 'status' => 'cancelled']);
        $this->assertDatabaseHas('order_items', ['id' => $line->id, 'quantity' => 2]);
    }

    public function test_user_cannot_view_or_open_the_edit_form_for_another_users_order(): void
    {
        $accountA = User::factory()->create();
        $accountB = User::factory()->create();
        $order = $this->createOrder($accountA, $this->makeItem());

        $this->actingAs($accountB)
            ->get(route('orders.show', $order))
            ->assertForbidden();
        $this->get(route('orders.edit', $order))->assertForbidden();
    }

    public function test_direct_update_complete_and_cancel_requests_cannot_change_another_users_order(): void
    {
        $accountA = User::factory()->create();
        $accountB = User::factory()->create();
        $item = $this->makeItem();
        $order = $this->createOrder($accountA, $item);
        $line = $order->orderItems->firstOrFail();

        $this->actingAs($accountB)
            ->put(route('orders.update', $order), [
                'items' => [[
                    'order_item_id' => $line->id,
                    'item_id' => $item->id,
                    'quantity' => 9,
                ]],
            ])
            ->assertForbidden();
        $this->patch(route('orders.complete', $order))->assertForbidden();
        $this->patch(route('orders.cancel', $order))->assertForbidden();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'user_id' => $accountA->id,
            'status' => 'pending',
            'total_amount' => '10.00',
        ]);
        $this->assertDatabaseHas('order_items', ['id' => $line->id, 'quantity' => 1, 'subtotal' => '10.00']);
    }

    public function test_service_mutations_are_scoped_to_the_supplied_authenticated_user_id(): void
    {
        $accountA = User::factory()->create();
        $accountB = User::factory()->create();
        $order = $this->createOrder($accountA, $this->makeItem());
        $service = app(OrderService::class);

        $this->assertNotFound(fn () => $service->update($order->id, $accountB->id, [
            'items' => [['item_id' => $order->orderItems->firstOrFail()->item_id, 'quantity' => 7]],
        ]));
        $this->assertNotFound(fn () => $service->complete($order->id, $accountB->id));
        $this->assertNotFound(fn () => $service->cancel($order->id, $accountB->id));

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'user_id' => $accountA->id, 'status' => 'pending']);
        $this->assertDatabaseHas('order_items', ['order_id' => $order->id, 'quantity' => 1]);
    }

    public function test_orders_list_and_dashboard_counts_are_limited_to_the_signed_in_user(): void
    {
        $accountA = User::factory()->create();
        $accountB = User::factory()->create();
        $item = $this->makeItem();
        $aOrder = $this->createOrder($accountA, $item);
        $bOrderOne = $this->createOrder($accountB, $item);
        $bOrderTwo = $this->createOrder($accountB, $item);

        $this->actingAs($accountA)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('#'.$aOrder->id)
            ->assertDontSee('#'.$bOrderOne->id)
            ->assertDontSee('#'.$bOrderTwo->id);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('1 order in your workspace.')
            ->assertDontSee('2 orders in your workspace.');

        $this->actingAs($accountB)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('#'.$bOrderOne->id)
            ->assertSee('#'.$bOrderTwo->id)
            ->assertDontSee('#'.$aOrder->id);
        $this->get(route('dashboard'))
            ->assertOk()
            ->assertSee('2 orders in your workspace.')
            ->assertDontSee('1 order in your workspace.');
    }

    public function test_order_creation_uses_the_authenticated_user_instead_of_a_submitted_user_id(): void
    {
        $accountA = User::factory()->create();
        $accountB = User::factory()->create();
        $item = $this->makeItem();

        $this->actingAs($accountA)
            ->post(route('orders.store'), [
                'user_id' => $accountB->id,
                'items' => [['item_id' => $item->id, 'quantity' => 1]],
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('orders', [
            'user_id' => $accountA->id,
            'status' => 'pending',
        ]);
        $this->assertDatabaseMissing('orders', [
            'user_id' => $accountB->id,
            'status' => 'pending',
        ]);
    }

    public function test_completed_and_cancelled_orders_keep_their_existing_status_restrictions(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem();
        $completed = $this->createOrder($user, $item);
        $cancelled = $this->createOrder($user, $item);
        $completed->update(['status' => OrderStatus::Completed]);
        $cancelled->update(['status' => OrderStatus::Cancelled]);

        $this->actingAs($user)
            ->put(route('orders.update', $completed), [
                'items' => [['item_id' => $item->id, 'quantity' => 2]],
            ])
            ->assertRedirect()
            ->assertSessionHas('error', 'Order cannot be edited because it is already completed.');
        $this->put(route('orders.update', $cancelled), [
            'items' => [['item_id' => $item->id, 'quantity' => 2]],
        ])->assertRedirect()
            ->assertSessionHas('error', 'Order cannot be edited because it is already cancelled.');
        $this->patch(route('orders.cancel', $completed))
            ->assertRedirect()
            ->assertSessionHas('error', 'Completed orders cannot be cancelled.');
        $this->patch(route('orders.complete', $cancelled))
            ->assertRedirect()
            ->assertSessionHas('error', 'Cancelled orders cannot be completed.');

        $this->assertDatabaseHas('orders', ['id' => $completed->id, 'status' => 'completed']);
        $this->assertDatabaseHas('orders', ['id' => $cancelled->id, 'status' => 'cancelled']);
    }

    private function makeItem(): Item
    {
        return Item::query()->create([
            'name' => 'Coffee',
            'description' => null,
            'price' => '10.00',
        ]);
    }

    private function createOrder(User $user, Item $item): Order
    {
        return app(OrderService::class)->create((int) $user->id, [
            'items' => [['item_id' => $item->id, 'quantity' => 1]],
        ]);
    }

    private function assertNotFound(callable $operation): void
    {
        try {
            $operation();
        } catch (ModelNotFoundException) {
            $this->addToAssertionCount(1);

            return;
        }

        $this->fail('Expected the user-scoped order lookup to return not found.');
    }
}
