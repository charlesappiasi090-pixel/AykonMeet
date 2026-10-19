<?php

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;

class StrongPassword implements ValidationRule
{
    /**
     * Run the validation rule.
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        $errors = [];

        if (strlen($value) < 8) {
            $errors[] = 'Password must be at least 8 characters long.';
        }

        if (! preg_match('/[A-Z]/', $value)) {
            $errors[] = 'Password must contain at least one uppercase letter.';
        }

        if (! preg_match('/[a-z]/', $value)) {
            $errors[] = 'Password must contain at least one lowercase letter.';
        }

        if (! preg_match('/[0-9]/', $value)) {
            $errors[] = 'Password must contain at least one number.';
        }

        if (! preg_match('/[^A-Za-z0-9]/', $value)) {
            $errors[] = 'Password must contain at least one special character.';
        }

        foreach ($errors as $error) {
            $fail($error);
        }
    }
}
