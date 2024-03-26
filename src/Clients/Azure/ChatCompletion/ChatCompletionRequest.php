<?php
namespace Cognesy\Instructor\Clients\Azure\ChatCompletion;

use Cognesy\Instructor\ApiClient\Data\Requests\ApiChatCompletionRequest;

class ChatCompletionRequest extends ApiChatCompletionRequest
{
    protected string $endpoint = '/chat/completions';

    public function getEndpoint(): string {
        return $this->endpoint;
    }
}