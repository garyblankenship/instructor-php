<?php

namespace Cognesy\Instructor\Events\PartialsGenerator;

use Cognesy\Instructor\ApiClient\Data\ToolCall;
use Cognesy\Instructor\Events\Event;
use Cognesy\Instructor\Utils\Json\Json;

class StreamedToolCallUpdated extends Event
{
    public function __construct(
        public ToolCall $toolCall
    ){
        parent::__construct();
    }

    public function __toString() : string
    {
        return Json::encode($this->toolCall);
    }
}
