<?php
namespace Tests;

use Cognesy\Instructor\Features\Http\Contracts\CanAccessResponse;
use Cognesy\Instructor\Features\Http\Contracts\CanHandleHttp;
use Cognesy\Instructor\Features\Http\Drivers\GuzzleDriver;
use Cognesy\Instructor\Features\LLM\Data\LLMResponse;
use Mockery;

class MockLLM
{
    static public function get(array $args) : CanHandleHttp {
//        $mockLLM = Mockery::mock(OpenAIDriver::class);
        $mockHttp = Mockery::mock(GuzzleDriver::class);
        $mockResponse = Mockery::mock(CanAccessResponse::class);

        $list = [];
        foreach ($args as $arg) {
            $list[] = self::makeFunc($arg);
        }

        $mockHttp->shouldReceive('handle')->andReturn($mockResponse);

//        $mockLLM->shouldReceive('getData')->andReturn('');
//        $mockLLM->shouldReceive('handle')->andReturn($mockResponse);
//        $mockLLM->shouldReceive('getEndpointUrl')->andReturn('');
//        $mockLLM->shouldReceive('getRequestHeaders')->andReturn([]);
//        $mockLLM->shouldReceive('getRequestBody')->andReturnUsing([]);
//        $mockLLM->shouldReceive('toLLMResponse')->andReturnUsing(...$list);
//        $mockLLM->shouldReceive('toPartialLLMResponse')->andReturn($mockLLM);

        $mockResponse->shouldReceive('getStatusCode')->andReturn(200);
        $mockResponse->shouldReceive('getHeaders')->andReturn([]);
        $mockResponse->shouldReceive('getContents')->andReturnUsing(...$list);
        $mockResponse->shouldReceive('streamContents')->andReturn($mockResponse);

        return $mockHttp;
    }

    static private function makeFunc(string $json) {
        return fn() => json_encode(self::mockOpenAIResponse($json));
    }

    static private function mockOpenAIResponse(string $json) : array {
        return [
            "id" => "chatcmpl-AGH2w25Kx4hNnqUgcxqcgnqrzfIaD",
            "object" => "chat.completion",
            "created" => 1728442138,
            "model" => "gpt-4o-mini-2024-07-18",
            "choices" => [
                0 => [
                    "index" => 0,
                    "message" => [
                        "role" => "assistant",
                        "content" => null,
                        "tool_calls" => [
                            0 => [
                                "id" => "call_HGWji0nx7LQsRGGw1ckosq6S",
                                "type" => "function",
                                "function" => [
                                    "name" => "extracted_data",
                                    "arguments" => $json,
                                ]
                            ]
                        ],
                        "refusal" => null,
                    ],
                    "logprobs" => null,
                    "finish_reason" => "stop",
                ]
            ],
            "usage" => [
                "prompt_tokens" => 95,
                "completion_tokens" => 9,
                "total_tokens" => 104,
                "prompt_tokens_details" => [
                    "cached_tokens" => 0,
                ],
                "completion_tokens_details" => [
                    "reasoning_tokens" => 0,
                ],
            ],
            "system_fingerprint" => "fp_f85bea6784",
        ];
    }
}
