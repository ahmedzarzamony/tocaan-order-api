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

        $class = $config['class'];
        if (!class_exists($class)) {
            return null;
        }

        return new $class($config);
    }
}
