<?php

namespace App\Services\Payment;


class PaymentGatewayFactory
{
    public static function make(string $method): PaymentGatewayInterface
    {
        return match ($method) {
            'credit_card' => new CreditCardGateway(),
            'paypal' => new PaypalGateway(),
            default => null
        };
    }
}
