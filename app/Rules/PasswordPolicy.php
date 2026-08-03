<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

/**
 * Password rule from the legacy `validate_password()` helper:
 * at least 8 characters and must include an uppercase letter or a symbol.
 */
class PasswordPolicy implements ValidationRule
{
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! $this->passes($value)) {
            $fail('The :attribute must be at least 8 characters and include an uppercase letter or a symbol.');
        }
    }

    private function passes(mixed $password): bool
    {
        $password = (string) $password;

        if (strlen($password) < 8) {
            return false;
        }

        $hasUpper = preg_match('/[A-Z]/', $password) === 1;
        $hasSymbol = preg_match('/[^A-Za-z0-9]/', $password) === 1;

        return $hasUpper || $hasSymbol;
    }
}