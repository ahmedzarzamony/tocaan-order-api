<?php

namespace Tests\Unit;

use App\Models\Payment;
use App\Services\Payment\CreditCardGateway;
use Tests\TestCase;

class CreditCardGatewayTest extends TestCase
{
    public function test_credit_card_gateway_process()
    {
        $payment = new Payment([
            'amount' => 200,
            'payment_method' => 'credit_card',
        ]);

        $gateway = new CreditCardGateway([
            'api_key' => 'test_key',
            'api_secret' => 'test_secret',
        ]);

        $response = $gateway->process($payment);

        $this->assertIsBool($response);
        $this->assertTrue($response);
    }
}
