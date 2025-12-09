<?php

namespace Database\Factories;

use App\Models\Order;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Payment>
 */
class PaymentFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'order_id' => Order::factory(), // ينشئ أوردر جديد لو مش محدد
            'payment_method' => $this->faker->randomElement(['credit_card', 'paypal']),
            'amount' => $this->faker->randomFloat(2, 10, 500),
            'status' => $this->faker->randomElement(['pending', 'successful', 'failed']),
        ];
    }

    /** حالة دفع ناجحة */
    public function successful(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'successful',
        ]);
    }

    /** حالة دفع فاشلة */
    public function failed(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'failed',
        ]);
    }

    /** حالة دفع معلقة */
    public function pending(): self
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'pending',
        ]);
    }
}
