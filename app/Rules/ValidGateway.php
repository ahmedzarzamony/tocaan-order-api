<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class ValidGateway implements ValidationRule
{
    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string=): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $gateways = array_keys(config('payment.gateways', []));

        if (! in_array($value, $gateways)) {
            $fail("The selected {$attribute} is invalid. Allowed: ".implode(', ', $gateways), null);
        }
    }
}
