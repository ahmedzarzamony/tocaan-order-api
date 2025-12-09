<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\Payment;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderPaymentTest extends TestCase
{
    use RefreshDatabase;

    public function test_authentication_required()
    {
        $response = $this->postJson('/api/orders', []);

        $response->assertStatus(401);
    }

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

    public function test_can_update_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->putJson("/api/orders/{$order->id}", [
            'items' => [
                ['product_name' => 'New Item', 'quantity' => 1, 'price' => 50],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order updated successfully']);

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'total' => 50,
        ]);
    }

    public function test_can_delete_order()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        $response = $this->actingAs($user, 'api')->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(200)
            ->assertJson(['message' => 'Order deleted successfully']);

        $this->assertDatabaseMissing('orders', ['id' => $order->id]);
    }

    public function test_cannot_delete_order_with_payments()
    {
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        Payment::factory()->create([
            'order_id' => $order->id,
            'status' => 'successful',
        ]);

        $response = $this->actingAs($user, 'api')
            ->deleteJson("/api/orders/{$order->id}");

        $response->assertStatus(403)
            ->assertJson(['message' => 'Cannot delete order with existing payments.']);
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
