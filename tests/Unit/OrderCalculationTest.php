<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Order;
use App\Models\OrderItem;

class OrderCalculationTest extends TestCase
{
    
    public function test_order_total_calculation()
    {
        $order = new Order();

        $order->items = collect([
            new OrderItem(['quantity' => 2, 'price' => 10]),
            new OrderItem(['quantity' => 1, 'price' => 5]),
        ]);

        $total = $order->items->sum(fn($item) => $item->quantity * $item->price);


        $this->assertEquals(25, $total);
    }
}
