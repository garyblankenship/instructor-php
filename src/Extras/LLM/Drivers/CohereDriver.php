<?php
namespace Cognesy\Instructor\Extras\LLM\Drivers;

use Cognesy\Instructor\ApiClient\Data\ToolCall;
use Cognesy\Instructor\ApiClient\Data\ToolCalls;
use Cognesy\Instructor\ApiClient\Responses\ApiResponse;
use Cognesy\Instructor\ApiClient\Responses\PartialApiResponse;
use Cognesy\Instructor\Data\Messages\Messages;
use Cognesy\Instructor\Enums\Mode;
use Cognesy\Instructor\Extras\LLM\Contracts\CanHandleHttp;
use Cognesy\Instructor\Extras\LLM\Contracts\CanHandleInference;
use Cognesy\Instructor\Extras\LLM\Data\LLMConfig;
use Cognesy\Instructor\Extras\LLM\InferenceRequest;
use Cognesy\Instructor\Utils\Json\Json;
use Psr\Http\Message\ResponseInterface;

class CohereDriver implements CanHandleInference
{
    public function __construct(
        protected CanHandleHttp $http,
        protected LLMConfig $config
    ) {}

    // REQUEST //////////////////////////////////////////////

    public function handle(InferenceRequest $request) : ResponseInterface {
        return $this->http->handle(
            url: $this->getEndpointUrl($request),
            headers: $this->getRequestHeaders(),
            body: $this->getRequestBody(
                $request->messages,
                $request->model,
                $request->tools,
                $request->toolChoice,
                $request->responseFormat,
                $request->options,
                $request->mode,
            ),
            streaming: $request->options['stream'] ?? false,
        );
    }

    public function getEndpointUrl(InferenceRequest $request) : string {
        return "{$this->config->apiUrl}{$this->config->endpoint}";
    }

    public function getRequestHeaders() : array {
        return [
            'Authorization' => "Bearer {$this->config->apiKey}",
            'Content-Type' => 'application/json',
        ];
    }

    public function getRequestBody(
        array $messages = [],
        string $model = '',
        array $tools = [],
        string|array $toolChoice = '',
        array $responseFormat = [],
        array $options = [],
        Mode $mode = Mode::Text,
    ) : array {
        $system = '';
        $chatHistory = [];

        $request = array_filter(array_merge([
            'model' => $model ?: $this->config->model,
            'preamble' => $system,
            'chat_history' => $chatHistory,
            'message' => Messages::asString($messages),
        ], $options));

        return $this->applyMode($request, $mode, $tools, $toolChoice, $responseFormat);
    }

    // RESPONSE /////////////////////////////////////////////

    public function toApiResponse(array $data): ApiResponse {
        return new ApiResponse(
            content: $this->makeContent($data),
            responseData: $data,
            toolName: $data['tool_calls'][0]['name'] ?? '',
            toolArgs: Json::encode($data['tool_calls'][0]['parameters'] ?? []),
            toolsData: $this->mapToolsData($data),
            finishReason: $data['finish_reason'] ?? '',
            toolCalls: $this->makeToolCalls($data),
            inputTokens: $data['meta']['tokens']['input_tokens'] ?? 0,
            outputTokens: $data['meta']['tokens']['output_tokens'] ?? 0,
            cacheCreationTokens: 0,
            cacheReadTokens: 0,
        );
    }

    public function toPartialApiResponse(array $data) : PartialApiResponse {
        return new PartialApiResponse(
            delta: $this->makeDelta($data),
            responseData: $data,
            toolName: $data['tool_calls'][0]['name'] ?? '',
            toolArgs: Json::encode($data['tool_calls'][0]['parameters'] ?? []),
            finishReason: $data['finish_reason'] ?? '',
            inputTokens: $data['message']['usage']['input_tokens'] ?? $data['usage']['input_tokens'] ?? 0,
            outputTokens: $data['message']['usage']['output_tokens'] ?? $data['usage']['input_tokens'] ?? 0,
            cacheCreationTokens: 0,
            cacheReadTokens: 0,
        );
    }

    public function getData(string $data): string|bool {
        $data = trim($data);
        return match(true) {
            $data === '[DONE]' => false,
            default => $data,
        };
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
                $request['tools'] = $this->toTools($tools);
                break;
            case Mode::Json:
                $request['response_format'] = [
                    'type' => 'json_object',
                    'schema' => $responseFormat['schema'] ?? [],
                ];
                break;
            case Mode::JsonSchema:
                $request['response_format'] = [
                    'type' => 'json_object',
                    'schema' => $responseFormat['json_schema']['schema'] ?? [],
                ];
                break;
        }
        return $request;
    }

    private function toTools(array $tools): array {
        $result = [];
        foreach ($tools as $tool) {
            $parameters = [];
            foreach ($tool['function']['parameters']['properties'] as $name => $param) {
                $parameters[$name] = array_filter([
                    'description' => $param['description'] ?? '',
                    'type' => $this->toCohereType($param),
                    'required' => in_array(
                        needle: $name,
                        haystack: $tool['function']['parameters']['required'] ?? [],
                    ),
                ]);
            }
            $result[] = [
                'name' => $tool['function']['name'],
                'description' => $tool['function']['description'] ?? '',
                'parameterDefinitions' => $parameters,
            ];
        }
        return $result;
    }

    private function toCohereType(array $param) : string {
        return match($param['type']) {
            'string' => 'str',
            'number' => 'float',
            'integer' => 'int',
            'boolean' => 'bool',
            'array' => throw new \Exception('Array type not supported by Cohere'),
            'object' => throw new \Exception('Object type not supported by Cohere'),
            default => throw new \Exception('Unknown type'),
        };
    }

    private function makeToolCalls(array $data) : ToolCalls {
        return ToolCalls::fromMapper(
            $data['tool_calls'] ?? [],
            fn($call) => ToolCall::fromArray(['name' => $call['name'] ?? '', 'arguments' => $call['parameters'] ?? ''])
        );
    }

    private function mapToolsData(array $data) : array {
        return array_map(
            fn($tool) => [
                'name' => $tool['name'] ?? '',
                'arguments' => $tool['parameters'] ?? '',
            ],
            $data['tool_calls'] ?? []
        );
    }

    private function makeContent(array $data) : string {
        return ($data['text'] ?? '') . (!empty($data['tool_calls'])
            ? ("\n" . Json::encode($data['tool_calls']))
            : ''
        );
    }

    private function makeDelta(array $data) : string {
        return $data['text'] ?? $data['tool_calls'][0]['parameters'] ?? '';
    }
}
