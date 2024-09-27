<?php
use Cognesy\Instructor\ApiClient\Enums\ClientType;
use Cognesy\Instructor\Extras\Enums\HttpClientType;
use Cognesy\Instructor\Utils\Env;

return [
    'useObjectReferences' => false,

    'debug' => [
        'enabled' => false,
        'http_trace' => false,
        'request_body' => true,
        'request_headers' => false,
        'response_body' => true,
        'response_headers' => false,
    ],

    'cache' => [
        'enabled' => false,
        'expiryInSeconds' => 3600,
        'path' => '/tmp/instructor/cache',
    ],

    'defaultConnection' => 'openai',
    'connections' => [
        'anthropic' => [
            'clientType' => ClientType::Anthropic->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://api.anthropic.com/v1',
            'apiKey' => Env::get('ANTHROPIC_API_KEY', ''),
            'endpoint' => '/messages',
            'metadata' => [
                'apiVersion' => '2023-06-01',
                'beta' => 'prompt-caching-2024-07-31',
            ],
            'defaultModel' => 'claude-3-haiku-20240307',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'azure' => [
            'clientType' => ClientType::Azure->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://{resourceName}.openai.azure.com/openai/deployments/{deploymentId}',
            'apiKey' => Env::get('AZURE_OPENAI_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'metadata' => [
                'apiVersion' => '2023-03-15-preview',
                'resourceName' => 'instructor-dev',
                'deploymentId' => 'gpt-4o-mini',
            ],
            'defaultModel' => 'gpt-4o-mini',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'cohere' => [
            'clientType' => ClientType::Cohere->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://api.cohere.ai/v1',
            'apiKey' => Env::get('COHERE_API_KEY', ''),
            'endpoint' => '/chat',
            'defaultModel' => 'command-r-plus-08-2024',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'fireworks' => [
            'clientType' => ClientType::Fireworks->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://api.fireworks.ai/inference/v1',
            'apiKey' => Env::get('FIREWORKS_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'defaultModel' => 'accounts/fireworks/models/mixtral-8x7b-instruct',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'gemini' => [
            'clientType' => ClientType::Gemini->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://generativelanguage.googleapis.com/v1beta',
            'apiKey' => Env::get('GEMINI_API_KEY', ''),
            'endpoint' => '/models/{model}:generateContent',
            'defaultModel' => 'gemini-1.5-flash-latest',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'groq' => [
            'clientType' => ClientType::Groq->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://api.groq.com/openai/v1',
            'apiKey' => Env::get('GROQ_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'defaultModel' => 'llama3-groq-8b-8192-tool-use-preview', // 'gemma2-9b-it',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'mistral' => [
            'clientType' => ClientType::Mistral->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://api.mistral.ai/v1',
            'apiKey' => Env::get('MISTRAL_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'defaultModel' => 'mistral-small-latest',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'ollama' => [
            'clientType' => ClientType::Ollama->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'http://localhost:11434/v1',
            'apiKey' => Env::get('OLLAMA_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'defaultModel' => 'llama3.2:3b', //'gemma2:2b',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 60,
        ],
        'openai' => [
            'clientType' => ClientType::OpenAI->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://api.openai.com/v1',
            'apiKey' => Env::get('OPENAI_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'metadata' => [
                'organization' => ''
            ],
            'defaultModel' => 'gpt-4o-mini',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'openrouter' => [
            'clientType' => ClientType::OpenRouter->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://openrouter.ai/api/v1/',
            'apiKey' => Env::get('OPENROUTER_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'defaultModel' => 'qwen/qwen-2.5-72b-instruct', //'microsoft/phi-3.5-mini-128k-instruct',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
        'together' => [
            'clientType' => ClientType::Together->value,
            'httpClient' => HttpClientType::Guzzle->value,
            'apiUrl' => 'https://api.together.xyz/v1',
            'apiKey' => Env::get('TOGETHER_API_KEY', ''),
            'endpoint' => '/chat/completions',
            'defaultModel' => 'mistralai/Mixtral-8x7B-Instruct-v0.1',
            'defaultMaxTokens' => 1024,
            'connectTimeout' => 3,
            'requestTimeout' => 30,
        ],
    ],
];
