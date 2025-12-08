<?php

namespace App\Services\Payment;


class PaymentGatewayFactory
{
    public static function make(string $method): PaymentGatewayInterface|null
    {
        $gatewayName = $method ?? config('payment.default_gateway');
        $config = config("payment.gateways.$gatewayName");

        if (!$config) {
            return null;
        }


        return match ($method) {
            'credit_card' => new CreditCardGateway($config),
            'paypal' => new PaypalGateway($config),
            default => null
        };
    }
}
