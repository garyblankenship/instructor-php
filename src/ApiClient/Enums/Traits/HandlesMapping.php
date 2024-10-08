<?php
namespace Cognesy\Instructor\ApiClient\Enums\Traits;

use Cognesy\Instructor\Clients\Anthropic\AnthropicApiRequest;
use Cognesy\Instructor\Clients\Anthropic\AnthropicClient;
//use Cognesy\Instructor\Clients\Anyscale\AnyscaleClient;
use Cognesy\Instructor\Clients\Azure\AzureApiRequest;
use Cognesy\Instructor\Clients\Azure\AzureClient;
use Cognesy\Instructor\Clients\Cohere\CohereApiRequest;
use Cognesy\Instructor\Clients\Cohere\CohereClient;
use Cognesy\Instructor\Clients\FireworksAI\FireworksAIApiRequest;
use Cognesy\Instructor\Clients\FireworksAI\FireworksAIClient;
use Cognesy\Instructor\Clients\Gemini\GeminiApiRequest;
use Cognesy\Instructor\Clients\Gemini\GeminiClient;
use Cognesy\Instructor\Clients\Groq\GroqApiRequest;
use Cognesy\Instructor\Clients\Groq\GroqClient;
use Cognesy\Instructor\Clients\Mistral\MistralApiRequest;
use Cognesy\Instructor\Clients\Mistral\MistralClient;
use Cognesy\Instructor\Clients\Ollama\OllamaApiRequest;
use Cognesy\Instructor\Clients\Ollama\OllamaClient;
use Cognesy\Instructor\Clients\OpenAI\OpenAIApiRequest;
use Cognesy\Instructor\Clients\OpenAI\OpenAIClient;
use Cognesy\Instructor\Clients\OpenRouter\OpenRouterApiRequest;
use Cognesy\Instructor\Clients\OpenRouter\OpenRouterClient;
use Cognesy\Instructor\Clients\TogetherAI\TogetherAIClient;
use Cognesy\Instructor\Clients\TogetherAI\TogetherApiRequest;
use Cognesy\Instructor\Enums\Mode;
use Cognesy\Instructor\Utils\Str;

trait HandlesMapping
{
    public function toNativeMessages(string|array $messages) : array {
        if (is_string($messages)) {
            $messages = [['role' => 'user', 'content' => $messages]];
        }
        $transformed = [];
        foreach ($messages as $message) {
            $transformed[] = $this->toNativeMessage($message);
        }
        return $transformed;
    }

    public function toNativeMessage(array $message) : array {
        return match($this) {
            self::Anthropic => ['role' => $this->mapRole($message['role']), 'content' => $this->toAnthropicContent($message['content'])],
            //self::Anyscale => $message,
            self::Azure => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::Cohere => ['role' => $this->mapRole($message['role']), 'message' => $message['content']],
            self::Fireworks => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::Gemini => ['role' => $this->mapRole($message['role']), "parts" => $this->toGeminiParts($message['content'])],
            self::Groq => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::Mistral => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::Ollama => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::OpenAI => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::OpenRouter => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::Together => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
            self::OpenAICompatible => ['role' => $this->mapRole($message['role']), 'content' => $this->toOpenAIContent($message['content'])],
        };
    }

    public function toClientClass() : string {
        return match($this) {
            self::Anthropic => AnthropicClient::class,
            //self::Anyscale => AnyscaleClient::class,
            self::Azure => AzureClient::class,
            self::Cohere => CohereClient::class,
            self::Fireworks => FireworksAIClient::class,
            self::Gemini => GeminiClient::class,
            self::Groq => GroqClient::class,
            self::Mistral => MistralClient::class,
            self::Ollama => OllamaClient::class,
            self::OpenAI => OpenAIClient::class,
            self::OpenRouter => OpenRouterClient::class,
            self::Together => TogetherAIClient::class,
            default => OpenAIClient::class,
        };
    }

    public function toRequestClass(Mode $mode = null) : string {
        return match($this) {
            self::Anthropic => AnthropicApiRequest::class,
            //self::Anyscale => AnyscaleApiRequest::class,
            self::Azure => AzureApiRequest::class,
            self::Cohere => CohereApiRequest::class,
            self::Fireworks => FireworksAIApiRequest::class,
            self::Gemini => GeminiApiRequest::class,
            self::Groq => GroqApiRequest::class,
            self::Mistral => MistralApiRequest::class,
            self::Ollama => OllamaApiRequest::class,
            self::OpenAI => OpenAIApiRequest::class,
            self::OpenRouter => OpenRouterApiRequest::class,
            self::Together => TogetherApiRequest::class,
            default => OpenAIApiRequest::class,
        };
    }

    // INTERNAL ////////////////////////////////////////////////////////////////////

    private function mapRole(string $role) : string {
        $map = $this->getRoleMap();
        return $map[$role] ?? $role;
    }

    private function getRoleMap() : array {
        return match($this) {
            self::Anthropic => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'user', 'tool' => 'user'],
            //self::Anyscale => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::Azure => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::Cohere => ['user' => 'USER', 'assistant' => 'CHATBOT', 'system' => 'USER', 'tool' => 'USER'],
            self::Fireworks => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::Gemini => ['user' => 'user', 'assistant' => 'model', 'system' => 'user', 'tool' => 'tool'],
            self::Groq => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::Mistral => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::Ollama => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::OpenAI => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::OpenRouter => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::Together => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
            self::OpenAICompatible => ['user' => 'user', 'assistant' => 'assistant', 'system' => 'system', 'tool' => 'tool'],
        };
    }

    private function toOpenAIContent(string|array $content) : string|array {
        if (is_string($content)) {
            return $content;
        }
        // if content is array - process each part
        $transformed = [];
        foreach ($content as $contentPart) {
            $transformed[] = $this->contentPartToOpenAI($contentPart);
        }
        return $transformed;
    }

    private function contentPartToOpenAI(array $contentPart) : array {
        $type = $contentPart['type'] ?? 'text';
        if ($type === 'text') {
            $contentPart = [
                'type' => 'text',
                'text' => $contentPart['text'],
            ];
        }
        return $contentPart;
    }

    private function toAnthropicContent(string|array $content) : string|array {
        if (is_string($content)) {
            return $content;
        }
        // if content is array - process each part
        $transformed = [];
        foreach ($content as $contentPart) {
            $transformed[] = $this->contentPartToAnthropic($contentPart);
        }
        return $transformed;
    }

    private function contentPartToAnthropic(array $contentPart) : array {
        $type = $contentPart['type'] ?? 'text';
        if ($type === 'image_url') {
            $mimeType = Str::between($contentPart['image_url']['url'], 'data:', ';base64,');
            $base64content = Str::after($contentPart['image_url']['url'], ';base64,');
            $contentPart = [
                'type' => 'image',
                'source' => [
                    'type' => 'base64',
                    'media_type' => $mimeType,
                    'data' => $base64content,
                ],
            ];
        }
        return $contentPart;
    }

    private function toGeminiParts(string|array $content) : array {
        if (is_string($content)) {
            return [["text" => $content]];
        }
        $transformed = [];
        foreach ($content as $contentPart) {
            $transformed[] = $this->contentPartToGemini($contentPart);
        }
        return $transformed;
    }

    private function contentPartToGemini(array $contentPart) : array {
        $type = $contentPart['type'] ?? 'text';
        return match($type) {
            'text' => ['text' => $contentPart['text']],
            'image_url' => [
                'inlineData' => [
                    'mimeType' => Str::between($contentPart['image_url']['url'], 'data:', ';base64,'),
                    'data' => Str::after($contentPart['image_url']['url'], ';base64,'),
                ],
            ],
            default => $contentPart,
        };
    }
}
