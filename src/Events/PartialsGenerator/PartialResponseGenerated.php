<?php

namespace Cognesy\Instructor\Events\PartialsGenerator;

use Cognesy\Instructor\Events\Event;
use Cognesy\Instructor\Utils\Json\Json;

class PartialResponseGenerated extends Event
{
    public function __construct(
        public mixed $partialResponse
    ) {
        parent::__construct();
    }

    public function __toString(): string
    {
        return Json::encode($this->partialResponse);
    }
}