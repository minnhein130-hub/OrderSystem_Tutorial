<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\User;
use App\Services\ItemService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ItemsTest extends TestCase
{
    use RefreshDatabase;

    public function test_items_routes_require_authentication(): void
    {
        $this->get(route('items.index'))->assertRedirect(route('login'));
        $this->get(route('items.create'))->assertRedirect(route('login'));
        $this->get(route('items.show', 1))->assertRedirect(route('login'));
        $this->get(route('items.edit', 1))->assertRedirect(route('login'));
        $this->post(route('items.store'))->assertRedirect(route('login'));
        $this->put(route('items.update', 1))->assertRedirect(route('login'));
        $this->delete(route('items.destroy', 1))->assertRedirect(route('login'));
    }

    public function test_item_create_and_edit_pages_can_be_opened(): void
    {
        $user = User::factory()->create();
        $item = Item::query()->create([
            'name' => 'Editable item',
            'description' => 'Item description',
            'price' => '10.00',
        ]);

        $this->actingAs($user)
            ->get(route('items.create'))
            ->assertOk()
            ->assertSee('Create Item');

        $this->get(route('items.edit', $item))
            ->assertOk()
            ->assertSee('Editable item')
            ->assertSee('Save Changes');
    }

    public function test_missing_item_ids_return_not_found_for_item_actions(): void
    {
        $user = User::factory()->create();
        $missingId = 999999;
        $payload = [
            'name' => 'Missing item',
            'description' => null,
            'price' => '10.00',
        ];

        $this->actingAs($user)
            ->get(route('items.show', $missingId))
            ->assertNotFound();

        $this->get(route('items.edit', $missingId))->assertNotFound();
        $this->put(route('items.update', $missingId), $payload)->assertNotFound();
        $this->delete(route('items.destroy', $missingId))->assertNotFound();
    }

    public function test_nonnumeric_item_route_parameters_return_not_found(): void
    {
        $user = User::factory()->create();
        $payload = [
            'name' => 'Invalid item route',
            'description' => null,
            'price' => '10.00',
        ];

        $this->actingAs($user)
            ->get('/items/not-a-number')
            ->assertNotFound();

        $this->get('/items/not-a-number/edit')->assertNotFound();
        $this->put('/items/not-a-number', $payload)->assertNotFound();
        $this->delete('/items/not-a-number')->assertNotFound();
    }

    public function test_items_list_can_search_names_and_descriptions_and_keep_search_in_pagination(): void
    {
        $user = User::factory()->create();

        Item::query()->create(['name' => 'Needle name', 'description' => 'First', 'price' => '10.00']);
        Item::query()->create(['name' => 'Other name', 'description' => 'Needle description', 'price' => '20.00']);
        Item::query()->create(['name' => 'Unmatched item', 'description' => 'Other text', 'price' => '30.00']);

        $response = $this->actingAs($user)->get(route('items.index', ['search' => 'Needle']));

        $response->assertOk()
            ->assertSee('Needle name')
            ->assertSee('Needle description')
            ->assertDontSee('Unmatched item');

        foreach (range(1, 11) as $number) {
            Item::query()->create([
                'name' => "Needle item {$number}",
                'description' => 'Pagination match',
                'price' => '1.00',
            ]);
        }

        $paginatedResponse = $this->actingAs($user)->get(route('items.index', ['search' => 'Needle']));

        $paginatedResponse->assertOk()->assertSee('search=Needle');
    }

    public function test_item_can_be_created_and_invalid_input_is_rejected(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post(route('items.store'), [
                'name' => 'Desk Lamp',
                'description' => 'Adjustable lamp',
                'price' => '19.95',
            ])
            ->assertRedirect(route('items.index'))
            ->assertSessionHas('success', 'Item created successfully.');

        $item = Item::query()->where('name', 'Desk Lamp')->firstOrFail();
        $this->assertSame('19.95', $item->price);

        $this->from(route('items.create'))
            ->post(route('items.store'), [
                'name' => '',
                'price' => '-1.00',
            ])
            ->assertRedirect(route('items.create'))
            ->assertSessionHasErrors(['name', 'price']);

        $this->assertDatabaseCount('items', 1);
    }

    public function test_item_can_be_viewed_and_updated(): void
    {
        $user = User::factory()->create();
        $item = Item::query()->create([
            'name' => 'Old Name',
            'description' => null,
            'price' => '10.00',
        ]);

        $this->actingAs($user)
            ->get(route('items.show', $item))
            ->assertOk()
            ->assertSee('Old Name')
            ->assertSee('Created');

        $this->put(route('items.update', $item), [
            'name' => 'New Name',
            'description' => 'Updated description',
            'price' => '25.50',
        ])->assertRedirect(route('items.show', $item))
            ->assertSessionHas('success', 'Item updated successfully.');

        $this->assertDatabaseHas('items', [
            'id' => $item->id,
            'name' => 'New Name',
            'description' => 'Updated description',
            'price' => '25.50',
        ]);
    }

    public function test_unused_item_can_be_deleted(): void
    {
        $user = User::factory()->create();
        $item = Item::query()->create([
            'name' => 'Temporary item',
            'description' => null,
            'price' => '1.00',
        ]);

        $this->actingAs($user)
            ->delete(route('items.destroy', $item))
            ->assertRedirect(route('items.index'))
            ->assertSessionHas('success', 'Item deleted successfully.');

        $this->assertDatabaseMissing('items', ['id' => $item->id]);
    }

    public function test_item_delete_blocked_by_an_order_shows_the_error_notification(): void
    {
        $user = User::factory()->create();
        $item = Item::query()->create([
            'name' => 'Ordered item',
            'description' => null,
            'price' => '1.00',
        ]);

        $itemService = \Mockery::mock(ItemService::class);
        $itemService->shouldReceive('find')->once()->with($item->id)->andReturn($item);
        $itemService->shouldReceive('delete')->once()->with($item)->andReturn(false);
        $this->instance(ItemService::class, $itemService);

        $this->actingAs($user)
            ->followingRedirects()
            ->delete(route('items.destroy', $item))
            ->assertOk()
            ->assertSee('This item cannot be deleted because it is used in an order.')
            ->assertSee('role="alert"', false)
            ->assertDatabaseHas('items', ['id' => $item->id]);
    }

    public function test_item_flash_notification_has_auto_dismiss_and_manual_dismiss_controls(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->withSession(['success' => 'Item created successfully.'])
            ->get(route('items.index'))
            ->assertOk()
            ->assertSee('Item created successfully.')
            ->assertSee('role="status"', false)
            ->assertSee('setTimeout(() => visible = false, 4000)', false)
            ->assertSee('Dismiss notification');
    }
}
