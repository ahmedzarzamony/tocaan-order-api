<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use App\Models\Order;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;
    
    public function test_can_create_order_with_items()
    {
        $user = User::factory()->create();
        $response = $this->actingAs($user, 'api')
            ->postJson('api/orders', [
                'items' => [
                    ['product_name' => 'Item 1', 'quantity' => 2, 'price' => 10],
                    ['product_name' => 'Item 2', 'quantity' => 1, 'price' => 5],
                ],
            ]);

        $response->assertStatus(201)
                 ->assertJson(['message' => 'Order created successfully']);

        $this->assertDatabaseHas('orders', ['user_id' => $user->id, 'total' => 25]);
        $this->assertDatabaseCount('order_items', 2);
    }

    public function test_cannot_pay_confirmed_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'confirmed']);

        $response = $this->actingAs($user, 'api')
            ->postJson('api/payments', [
                'order_id' => $order->id,
                'payment_method' => 'credit_card',
            ]); 

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Payments can only be processed for confirmed orders.']);
    }

    public function test_can_pay_pending_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        $response = $this->actingAs($user, 'api')
            ->postJson('api/payments', [
                'order_id' => $order->id,
                'payment_method' => 'credit_card',
            ]);

        $response->assertStatus(200)
                 ->assertJson(['message' => 'Payment processed successfully.']);

        $this->assertDatabaseHas('payments', [
            'order_id' => $order->id,
            'status' => 'successful',
        ]);
    }

    public function test_cannot_pay_order_twice()
    {
        $user = User::factory()->create();    
        $order = Order::factory()->create(['user_id' => $user->id, 'status' => 'pending']);

        // دفع أول مرة
        $this->actingAs($user, 'api')
             ->postJson('api/payments', [
                 'order_id' => $order->id,
                 'payment_method' => 'credit_card',
             ]);

        // دفع ثاني مرة
        $response = $this->actingAs($user, 'api')
            ->postJson('api/payments', [
                'order_id' => $order->id,
                'payment_method' => 'credit_card',
            ]);

        $response->assertStatus(403)
                 ->assertJson(['message' => 'Order has already been paid.']);
    }


}
