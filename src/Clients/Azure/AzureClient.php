<?php
namespace Cognesy\Instructor\Clients\Azure;

use Cognesy\Instructor\ApiClient\LLMClient;
use Cognesy\Instructor\ApiClient\LLMConnector;
use Cognesy\Instructor\Events\EventDispatcher;

class AzureClient extends LLMClient
{
    public function __construct(
        protected string $apiKey = '',
        protected string $baseUri = '',
        protected int    $connectTimeout = 3,
        protected int    $requestTimeout = 30,
        protected array  $metadata = [],
        EventDispatcher  $events = null,
        LLMConnector     $connector = null,
    ) {
        parent::__construct($events);

        $resourceName = $metadata['resourceName'] ?? '';
        $deploymentId = $metadata['deploymentId'] ?? '';
        $apiVersion = $metadata['apiVersion'] ?? '';

        $this->withConnector($connector ?? new AzureConnector(
            apiKey: $apiKey,
            resourceName: $resourceName,
            deploymentId: $deploymentId,
            baseUrl: $baseUri,
            connectTimeout: $connectTimeout,
            requestTimeout: $requestTimeout,
            metadata: $metadata,
            senderClass: '',
        ));
        $this->queryParams = ['api-version' => $apiVersion];
    }
}
