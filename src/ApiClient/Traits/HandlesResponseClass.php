<?php

namespace Cognesy\Instructor\ApiClient\Traits;

use Cognesy\Instructor\ApiClient\Responses\ApiResponse;
use Cognesy\Instructor\ApiClient\Responses\PartialApiResponse;
use Saloon\Http\Response;

trait HandlesResponseClass
{
    /** @var class-string */
    protected string $responseClass;
    /** @var class-string */
    protected string $partialResponseClass;

    protected function makeResponse(Response $response) : ApiResponse {
        return ($this->responseClass)::fromResponse($response);
    }

    protected function makePartialResponse(string $partialData) : PartialApiResponse {
        return ($this->partialResponseClass)::fromPartialResponse($partialData);
    }
}