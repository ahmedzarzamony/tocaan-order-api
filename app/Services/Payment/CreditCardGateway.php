<?php

namespace App\Services\Payment;

use App\Models\Payment;

class CreditCardGateway implements PaymentGatewayInterface
{
    public function process(Payment $payment): bool
    {
        return true;
    }
}
