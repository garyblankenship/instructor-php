<?php

namespace Cognesy\Instructor\Utils\Profiler;

class Checkpoint
{
    public function __construct(
        public string $name,
        public float $time,
        public float $delta,
        public bool $debug,
        public array $context,
    ) {}

    public function mili() : float {
        return $this->delta * 1_000;
    }

    public function micro() : float {
        return $this->delta * 1_000_000;
    }
}