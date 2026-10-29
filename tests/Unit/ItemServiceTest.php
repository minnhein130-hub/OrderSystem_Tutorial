<?php

namespace Tests\Unit;

use App\Repositories\Contracts\ItemRepositoryInterface;
use App\Services\ItemService;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;
use PHPUnit\Framework\TestCase;

class ItemServiceTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function test_item_is_not_deleted_when_an_order_uses_it(): void
    {
        $repository = Mockery::mock(ItemRepositoryInterface::class);
        $repository->shouldReceive('isUsedInOrder')->once()->with(123)->andReturn(true);
        $repository->shouldNotReceive('delete');

        $service = new ItemService($repository);

        $this->assertFalse($service->delete(123));
    }
}
