<?php
namespace Cognesy\Instructor\Clients\Groq\JsonCompletion;

use Cognesy\Instructor\ApiClient\Requests\ApiJsonCompletionRequest;

class JsonCompletionRequest extends ApiJsonCompletionRequest
{
    protected string $endpoint = '/chat/completions';

    public function __construct(
        public string|array  $messages = [],
        public array  $responseFormat = [],
        public string $model = '',
        public array  $options = [],
    ) {
        parent::__construct(
            $messages,
            $responseFormat,
            $model,
            $options,
        );
    }

    protected function getResponseFormat(): array {
        return ['type' => 'json_object'];
    }
}
