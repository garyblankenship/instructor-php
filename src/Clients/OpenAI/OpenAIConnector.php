<?php
namespace Cognesy\Instructor\Clients\OpenAI;

use Cognesy\Instructor\ApiClient\ApiConnector;
use Cognesy\Instructor\Events\EventDispatcher;
use Saloon\Contracts\Authenticator;
use Saloon\Http\Auth\TokenAuthenticator;

class OpenAIConnector extends ApiConnector
{
    protected string $baseUrl = 'https://api.openai.com/v1';
    protected string $organization;

    public function __construct(
        string $apiKey,
        string $baseUrl = '',
        string $organization = '',
        int    $connectTimeout = 3,
        int    $requestTimeout = 30,
        array  $metadata = [],
        string $senderClass = '',
        EventDispatcher $events = null,
    ) {
        parent::__construct($apiKey, $baseUrl, $connectTimeout, $requestTimeout, $metadata, $senderClass, $events);
        $this->organization = $organization;
    }

    protected function defaultAuth() : Authenticator {
        return new TokenAuthenticator($this->apiKey);
    }

    protected function defaultHeaders(): array {
        $headers = [
            'content-type' => 'application/json',
            'accept' => 'application/json',
            'OpenAI-Organization' => $this->organization,
        ];
        return $headers;
    }
}
