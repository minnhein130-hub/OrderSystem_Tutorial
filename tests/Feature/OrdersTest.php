<?php

namespace Tests\Feature;

use App\Enums\OrderStatus;
use App\Models\Item;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use App\Repositories\Contracts\OrderRepositoryInterface;
use App\Repositories\EloquentOrderRepository;
use App\Services\OrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;
use Tests\TestCase;

class OrdersTest extends TestCase
{
    use RefreshDatabase;

    public function test_orders_routes_require_authentication(): void
    {
        $item = $this->makeItem('Coffee', '6.80');
        $order = $this->makeOrder($item);

        $this->get(route('orders.index'))->assertRedirect(route('login'));
        $this->get(route('orders.create'))->assertRedirect(route('login'));
        $this->post(route('orders.store'), ['items' => [['item_id' => $item->id, 'quantity' => 1]]])
            ->assertRedirect(route('login'));
        $this->get(route('orders.show', $order))->assertRedirect(route('login'));
        $this->get(route('orders.edit', $order))->assertRedirect(route('login'));
        $this->put(route('orders.update', $order), ['items' => [['item_id' => $item->id, 'quantity' => 1]]])
            ->assertRedirect(route('login'));
        $this->patch(route('orders.complete', $order))->assertRedirect(route('login'));
        $this->patch(route('orders.cancel', $order))->assertRedirect(route('login'));
    }

    public function test_authenticated_user_can_view_orders_and_filter_by_id_and_status(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '6.80');
        $pending = $this->makeOrder($item, $user);
        $completed = $this->makeOrder($item, $user, OrderStatus::Completed);

        $this->actingAs($user)
            ->get(route('orders.index'))
            ->assertOk()
            ->assertSee('#'.$pending->id)
            ->assertSee('#'.$completed->id)
            ->assertSee('Created By')
            ->assertSee('Complete');

        $this->get(route('orders.index', ['search' => $completed->id, 'status' => 'completed']))
            ->assertOk()
            ->assertSee('#'.$completed->id)
            ->assertDontSee('#'.$pending->id);
    }

    public function test_order_can_be_created_with_multiple_items_and_server_calculated_total(): void
    {
        $user = User::factory()->create();
        $juice = $this->makeItem('Strawberry Juice', '12000.00');
        $coffee = $this->makeItem('Coffee', '6800.00');

        $this->actingAs($user)
            ->post(route('orders.store'), [
                'items' => [
                    ['item_id' => $juice->id, 'quantity' => 2],
                    ['item_id' => $coffee->id, 'quantity' => 3],
                ],
                'total_amount' => '0.01',
            ])
            ->assertRedirect();

        $order = Order::query()->latest('id')->firstOrFail();
        $this->assertSame((int) $user->id, (int) $order->user_id);
        $this->assertSame(OrderStatus::Pending, $order->status);
        $this->assertSame('44400.00', $order->total_amount);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_id' => $juice->id,
            'quantity' => 2,
            'unit_price' => '12000.00',
            'subtotal' => '24000.00',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_id' => $coffee->id,
            'quantity' => 3,
            'unit_price' => '6800.00',
            'subtotal' => '20400.00',
        ]);
    }

    public function test_invalid_quantities_are_rejected(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '6.80');

        $this->actingAs($user)
            ->from(route('orders.create'))
            ->post(route('orders.store'), ['items' => [['item_id' => $item->id, 'quantity' => 0]]])
            ->assertRedirect(route('orders.create'))
            ->assertSessionHasErrors('items.0.quantity');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_invalid_item_ids_are_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->from(route('orders.create'))
            ->post(route('orders.store'), ['items' => [['item_id' => 999999, 'quantity' => 1]]])
            ->assertRedirect(route('orders.create'))
            ->assertSessionHasErrors('items.0.item_id');

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_duplicate_item_ids_are_rejected_with_a_clear_message(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '6.80');

        $this->actingAs($user)
            ->from(route('orders.create'))
            ->post(route('orders.store'), [
                'items' => [
                    ['item_id' => $item->id, 'quantity' => 1],
                    ['item_id' => $item->id, 'quantity' => 2],
                ],
            ])
            ->assertRedirect(route('orders.create'))
            ->assertSessionHasErrors(['items.1.item_id' => 'Choose each item only once per order.']);

        $this->assertDatabaseCount('orders', 0);
    }

    public function test_failed_order_item_write_rolls_back_the_entire_order(): void
    {
        $user = User::factory()->create();
        $first = $this->makeItem('First', '10.00');
        $second = $this->makeItem('Second', '20.00');
        $this->app->instance(OrderRepositoryInterface::class, new FailOnSecondOrderItemRepository());

        try {
            $this->app->make(OrderService::class)->create($user->id, [
                'items' => [
                    ['item_id' => $first->id, 'quantity' => 1],
                    ['item_id' => $second->id, 'quantity' => 1],
                ],
            ]);
            $this->fail('Expected the second order item write to fail.');
        } catch (RuntimeException $exception) {
            $this->assertSame('Simulated order item write failure.', $exception->getMessage());
        }

        $this->assertDatabaseCount('orders', 0);
        $this->assertDatabaseCount('order_items', 0);
    }

    public function test_pending_order_can_be_edited_and_retained_line_keeps_its_price_snapshot(): void
    {
        $user = User::factory()->create();
        $coffee = $this->makeItem('Coffee', '10.00');
        $juice = $this->makeItem('Juice', '7.50');
        $order = $this->makeOrder($coffee, $user, OrderStatus::Pending, 2);
        $line = $order->orderItems()->firstOrFail();
        $coffee->update(['price' => '20.00']);

        $this->actingAs($user)
            ->get(route('orders.edit', $order))
            ->assertOk()
            ->assertSee('historical unit price');

        $this->put(route('orders.update', $order), [
            'items' => [
                ['order_item_id' => $line->id, 'item_id' => $coffee->id, 'quantity' => 3],
                ['item_id' => $juice->id, 'quantity' => 2],
            ],
        ])->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success', 'Order updated successfully.');

        $this->assertDatabaseHas('order_items', [
            'id' => $line->id,
            'item_id' => $coffee->id,
            'quantity' => 3,
            'unit_price' => '10.00',
            'subtotal' => '30.00',
        ]);
        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_id' => $juice->id,
            'quantity' => 2,
            'unit_price' => '7.50',
            'subtotal' => '15.00',
        ]);
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'total_amount' => '45.00']);
    }

    public function test_completed_orders_cannot_be_edited_even_through_direct_http_requests(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '10.00');
        $order = $this->makeOrder($item, $user, OrderStatus::Completed, 2);
        $line = $order->orderItems()->firstOrFail();

        $this->actingAs($user)
            ->get(route('orders.edit', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('error', 'Order cannot be edited because it is already completed.');

        $this->put(route('orders.update', $order), [
            'items' => [['order_item_id' => $line->id, 'item_id' => $item->id, 'quantity' => 9]],
        ])->assertRedirect()
            ->assertSessionHas('error', 'Order cannot be edited because it is already completed.');

        $this->assertDatabaseHas('order_items', ['id' => $line->id, 'quantity' => 2]);
    }

    public function test_pending_order_can_be_completed_but_cannot_be_cancelled_afterward(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '10.00');
        $order = $this->makeOrder($item, $user);

        $this->actingAs($user)
            ->patch(route('orders.complete', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success', 'Order completed successfully.');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'completed']);
        $this->patch(route('orders.cancel', $order))
            ->assertRedirect()
            ->assertSessionHas('error', 'Completed orders cannot be cancelled.');
    }

    public function test_pending_order_can_be_cancelled_and_cancelled_order_cannot_be_completed(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '10.00');
        $order = $this->makeOrder($item, $user);

        $this->actingAs($user)
            ->patch(route('orders.cancel', $order))
            ->assertRedirect(route('orders.show', $order))
            ->assertSessionHas('success', 'Order cancelled successfully.');

        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
        $this->patch(route('orders.complete', $order))
            ->assertRedirect()
            ->assertSessionHas('error', 'Cancelled orders cannot be completed.');
        $this->assertDatabaseHas('orders', ['id' => $order->id, 'status' => 'cancelled']);
    }

    public function test_historical_prices_do_not_change_when_catalog_price_changes(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '6.80');
        $order = $this->makeOrder($item, $user, OrderStatus::Pending, 3);

        $item->update(['price' => '8.25']);

        $this->assertDatabaseHas('order_items', [
            'order_id' => $order->id,
            'item_id' => $item->id,
            'unit_price' => '6.80',
            'subtotal' => '20.40',
        ]);
        $this->actingAs($user)
            ->get(route('orders.show', $order))
            ->assertOk()
            ->assertSee('6.80')
            ->assertSee('20.40');
    }

    public function test_item_referenced_by_an_order_cannot_be_deleted(): void
    {
        $user = User::factory()->create();
        $item = $this->makeItem('Coffee', '6.80');
        $this->makeOrder($item, $user);

        $this->actingAs($user)
            ->delete(route('items.destroy', $item))
            ->assertRedirect(route('items.show', $item))
            ->assertSessionHas('error', 'This item cannot be deleted because it is used in an order.');

        $this->assertDatabaseHas('items', ['id' => $item->id]);
    }

    private function makeItem(string $name, string $price): Item
    {
        return Item::query()->create([
            'name' => $name,
            'description' => null,
            'price' => $price,
        ]);
    }

    private function makeOrder(
        Item $item,
        ?User $user = null,
        OrderStatus $status = OrderStatus::Pending,
        int $quantity = 1,
    ): Order {
        [$whole, $fraction] = array_pad(explode('.', (string) $item->price, 2), 2, '0');
        $unitPriceCents = ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
        $subtotalCents = $unitPriceCents * $quantity;
        $format = static fn (int $cents): string => intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);

        $order = Order::query()->create([
            'user_id' => ($user ?? User::factory()->create())->id,
            'status' => $status,
            'total_amount' => $format($subtotalCents),
        ]);

        OrderItem::query()->create([
            'order_id' => $order->id,
            'item_id' => $item->id,
            'quantity' => $quantity,
            'unit_price' => $item->price,
            'subtotal' => $format($subtotalCents),
        ]);

        return $order->refresh();
    }
}

class FailOnSecondOrderItemRepository extends EloquentOrderRepository
{
    private int $createCount = 0;

    public function createOrderItem(Order $order, array $data): OrderItem
    {
        if (++$this->createCount === 2) {
            throw new RuntimeException('Simulated order item write failure.');
        }

        return parent::createOrderItem($order, $data);
    }
}
