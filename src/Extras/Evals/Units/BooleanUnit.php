<?php

namespace Cognesy\Instructor\Extras\Evals\Units;

use Cognesy\Instructor\Extras\Evals\Contracts\Unit;

class BooleanUnit implements Unit
{
    public function __construct(
        private string $name = '',
        private string $trueValue = 'yes',
        private string $falseValue = 'no',
    ) {}

    public function name(): string {
        return $this->name;
    }

    public function isValid(mixed $value): bool {
        return is_bool($value);
    }

    public function toString(mixed $value, array $format = []): string {
        $trueValue = $format['true'] ?? $this->trueValue;
        $falseValue = $format['false'] ?? $this->falseValue;
        return $value ? $trueValue : $falseValue;
    }

    public function toFloat(mixed $value): float {
        return $value ? 1.0 : 0.0;
    }
}
