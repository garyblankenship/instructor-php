<?php

namespace Cognesy\Instructor\Extras\Module\Core\Contracts;

use Cognesy\Instructor\Extras\Module\Core\Enums\TaskStatus;

interface CanProcessInput
{
    public function inputs() : array;
    //public function withContext(array $context) : static;
    public function status() : TaskStatus;
    public function outputs() : ?array;

    public function onSuccess(callable $callback) : static;
    public function onFailure(callable $callback) : static;
}