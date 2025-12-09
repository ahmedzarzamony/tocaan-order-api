<?php

namespace Tests\Unit;

use Tests\TestCase;
use App\Models\Payment;
use App\Services\Payment\PaypalGateway;

class PaypalGatewayTest extends TestCase
{
    public function test_paypal_gateway_process()
    {
        $payment = new Payment([
            'amount' => 150,
            'payment_method' => 'paypal'
        ]);

        $gateway = new PaypalGateway([
            'client_id' => '12345',
            'client_secret' => 'abcdef',
        ]);

        $response = $gateway->process($payment);

        $this->assertIsBool($response);
        $this->assertTrue($response);
    }
}
