<?php

namespace Tests\Unit;

use App\Services\Payment\CreditCardGateway;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaypalGateway;
use Tests\TestCase;

class GatewayFactoryTest extends TestCase
{
    public function test_factory_returns_credit_card_gateway()
    {
        $gateway = PaymentGatewayFactory::make('credit_card');
        $this->assertInstanceOf(CreditCardGateway::class, $gateway);
    }

    public function test_factory_returns_paypal_gateway()
    {
        $gateway = PaymentGatewayFactory::make('paypal');
        $this->assertInstanceOf(PaypalGateway::class, $gateway);
    }

    public function test_factory_returns_null_for_invalid_gateway()
    {
        $gateway = PaymentGatewayFactory::make('invalid_gateway');
        $this->assertNull($gateway);
    }
}
