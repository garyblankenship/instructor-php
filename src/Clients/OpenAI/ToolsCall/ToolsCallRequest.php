<?php
namespace Cognesy\Instructor\Clients\OpenAI\ToolsCall;

use Cognesy\Instructor\ApiClient\Data\Requests\ApiToolsCallRequest;

class ToolsCallRequest extends ApiToolsCallRequest
{
    protected string $endpoint = '/chat/completions';

    public function getEndpoint(): string {
        return $this->endpoint;
    }
}