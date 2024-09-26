<?php
namespace Cognesy\Instructor\Extras\LLM\Drivers;

use Cognesy\Instructor\ApiClient\Data\ToolCalls;
use Cognesy\Instructor\ApiClient\Responses\ApiResponse;
use Cognesy\Instructor\ApiClient\Responses\PartialApiResponse;
use Cognesy\Instructor\Enums\Mode;
use Cognesy\Instructor\Extras\LLM\Data\LLMConfig;
use Cognesy\Instructor\Extras\LLM\Contracts\CanHandleInference;
use Cognesy\Instructor\Extras\LLM\InferenceRequest;
use Cognesy\Instructor\Utils\Json\Json;
use GuzzleHttp\Client;

class OpenAIDriver implements CanHandleInference
{
    use Traits\HandlesHttpClient;

    public function __construct(
        protected Client $client,
        protected LLMConfig $config
    ) {}

    public function toApiResponse(array $data): ApiResponse {
        return new ApiResponse(
            content: $this->makeContent($data),
            responseData: $data,
            toolName: $data['choices'][0]['message']['tool_calls'][0]['function']['name'] ?? '',
            toolArgs: $data['choices'][0]['message']['tool_calls'][0]['function']['arguments'] ?? '',
            toolsData: $this->mapToolsData($data),
            finishReason: $data['choices'][0]['finish_reason'] ?? '',
            toolCalls: $this->makeToolCalls($data),
            inputTokens: $data['usage']['prompt_tokens'] ?? 0,
            outputTokens: $data['usage']['completion_tokens'] ?? 0,
            cacheCreationTokens: 0,
            cacheReadTokens: 0,
        );
    }

    public function toPartialApiResponse(array $data) : PartialApiResponse {
        return new PartialApiResponse(
            delta: $this->makeDelta($data),
            responseData: $data,
            toolName: $data['choices'][0]['delta']['tool_calls'][0]['function']['name'] ?? '',
            toolArgs: $data['choices'][0]['delta']['tool_calls'][0]['function']['arguments'] ?? '',
            finishReason: $data['choices'][0]['finish_reason'] ?? '',
            inputTokens: $data['usage']['prompt_tokens'] ?? 0,
            outputTokens: $data['usage']['completion_tokens'] ?? 0,
            cacheCreationTokens: 0,
            cacheReadTokens: 0,
        );
    }

    public function getData(string $data): string|bool {
        if (!str_starts_with($data, 'data:')) {
            return '';
        }
        $data = trim(substr($data, 5));
        return match(true) {
            $data === '[DONE]' => false,
            default => $data,
        };
    }

    // INTERNAL /////////////////////////////////////////////

    protected function getEndpointUrl(InferenceRequest $request): string {
        return "{$this->config->apiUrl}{$this->config->endpoint}";
    }

    protected function getRequestHeaders() : array {
        return [
            'Authorization' => "Bearer {$this->config->apiKey}",
            'Content-Type' => 'application/json',
        ];
    }

    protected function getRequestBody(
        array $messages = [],
        string $model = '',
        array $tools = [],
        string|array $toolChoice = '',
        array $responseFormat = [],
        array $options = [],
        Mode $mode = Mode::Text,
    ) : array {
        $request = array_filter(array_merge([
            'model' => $model ?: $this->config->model,
            'max_tokens' => $this->config->maxTokens,
            'messages' => $messages,
        ], $options));

        if ($options['stream'] ?? false) {
            $request['stream_options']['include_usage'] = true;
        }

        return $this->applyMode($request, $mode, $tools, $toolChoice, $responseFormat);
    }

    // PRIVATE //////////////////////////////////////////////

    private function applyMode(
        array $request,
        Mode $mode,
        array $tools,
        string|array $toolChoice,
        array $responseFormat
    ) : array {
        switch($mode) {
            case Mode::Tools:
                $request['tools'] = $tools;
                $request['tool_choice'] = $toolChoice;
                break;
            case Mode::Json:
                $request['response_format'] = ['type' => 'json_object'];
                break;
            case Mode::JsonSchema:
                $request['response_format'] = $responseFormat;
                break;
            case Mode::Text:
            case Mode::MdJson:
                $request['response_format'] = ['type' => 'text'];
                break;
        }
        return $request;
    }

    protected function toNativeContent(string|array $content) : string|array {
        if (is_string($content)) {
            return $content;
        }
        // if content is array - process each part
        $transformed = [];
        foreach ($content as $contentPart) {
            $transformed[] = $this->contentPartToNative($contentPart);
        }
        return $transformed;
    }

    protected function contentPartToNative(array $contentPart) : array {
        $type = $contentPart['type'] ?? 'text';
        if ($type === 'text') {
            $contentPart = [
                'type' => 'text',
                'text' => $contentPart['text'],
            ];
        }
        return $contentPart;
    }

    private function makeToolCalls(array $data) : ToolCalls {
        return ToolCalls::fromArray(array_map(
            callback: fn(array $call) => $call['function'] ?? [],
            array: $data['choices'][0]['message']['tool_calls'] ?? []
        ));
    }

    private function mapToolsData(array $data) : array {
        return array_map(
            fn($tool) => [
                'name' => $tool['function']['name'] ?? '',
                'arguments' => Json::parse($tool['function']['arguments']) ?? '',
            ],
            $data['choices'][0]['message']['tool_calls'] ?? []
        );
    }

    private function makeContent(array $data): string {
        $contentMsg = $data['choices'][0]['message']['content'] ?? '';
        $contentFnArgs = $data['choices'][0]['message']['tool_calls'][0]['function']['arguments'] ?? '';
        return match(true) {
            !empty($contentMsg) => $contentMsg,
            !empty($contentFnArgs) => $contentFnArgs,
            default => ''
        };
    }

    private function makeDelta(array $data): string {
        $deltaContent = $data['choices'][0]['delta']['content'] ?? '';
        $deltaFnArgs = $data['choices'][0]['delta']['tool_calls'][0]['function']['arguments'] ?? '';
        return match(true) {
            ('' !== $deltaContent) => $deltaContent,
            ('' !== $deltaFnArgs) => $deltaFnArgs,
            default => ''
        };
    }
}
