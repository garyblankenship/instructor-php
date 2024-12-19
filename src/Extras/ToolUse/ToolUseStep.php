<?php

namespace Cognesy\Instructor\Extras\ToolUse;

use Cognesy\Instructor\Features\LLM\Data\LLMResponse;
use Cognesy\Instructor\Features\LLM\Data\ToolCalls;
use Cognesy\Instructor\Features\LLM\Data\Usage;
use Cognesy\Instructor\Features\LLM\Enums\LLMFinishReason;
use Cognesy\Instructor\Utils\Messages\Messages;
use Throwable;

class ToolUseStep
{
    private string $response;
    private ?ToolCalls $toolCalls;
    private ?ToolExecutions $toolExecutions;
    private ?Messages $messages;
    private ?Usage $usage;
    private ?LLMResponse $llmResponse;

    public function __construct(
        string         $response = '',
        ToolCalls      $toolCalls = null,
        ToolExecutions $toolExecutions = null,
        Messages       $messages = null,
        Usage          $usage = null,
        LLMResponse    $llmResponse = null,
    ) {
        $this->response = $response;
        $this->toolCalls = $toolCalls;
        $this->toolExecutions = $toolExecutions;
        $this->messages = $messages;
        $this->usage = $usage;
        $this->llmResponse = $llmResponse;
    }

    public function response() : string {
        return $this->response ?? '';
    }

    public function messages() : Messages {
        return $this->messages ?? new Messages();
    }

    public function toolExecutions() : ToolExecutions {
        return $this->toolExecutions ?? new ToolExecutions();
    }

    public function usage() : Usage {
        return $this->usage ?? new Usage();
    }

    public function finishReason() : ?LLMFinishReason {
        return $this->llmResponse?->finishReason();
    }

    public function llmResponse() : ?LLMResponse {
        return $this->llmResponse;
    }

    // HANDLE TOOL CALLS ////////////////////////////////////////////

    public function toolCalls() : ToolCalls {
        return $this->toolCalls ?? new ToolCalls();
    }

    public function hasToolCalls() : bool {
        return $this->toolCalls()->count() > 0;
    }

    // HANDLE ERRORS ////////////////////////////////////////////////

    public function hasErrors() : bool {
        return match($this->toolExecutions) {
            null => false,
            default => $this->toolExecutions->hasErrors(),
        };
    }

    /**
     * @return Throwable[]
     */
    public function errors() : array {
        return $this->toolExecutions?->errors() ?? [];
    }

    public function errorsAsString() : string {
        return implode("\n", array_map(
            callback: fn(Throwable $e) => $e->getMessage(),
            array: $this->errors(),
        ));
    }

    public function errorExecutions() : ToolExecutions {
        return match($this->toolExecutions) {
            null => new ToolExecutions(),
            default => new ToolExecutions($this->toolExecutions->withErrors()),
        };
    }
}