<?php
namespace Cognesy\Instructor\ApiClient\Utils;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Http\PendingRequest;
use Saloon\Http\Response;
use Symfony\Component\VarDumper\VarDumper;

class Debugger
{
    public static function requestDebugger(
        PendingRequest $pendingRequest,
        RequestInterface $psrRequest
    ): void {
        $headers = self::getRequestHeaders($psrRequest);
        $className = explode('\\', $pendingRequest->getRequest()::class);
        $label = end($className);

        VarDumper::dump([
            'connector' => $pendingRequest->getConnector()::class,
            'request' => $pendingRequest->getRequest()::class,
            'method' => $psrRequest->getMethod(),
            'uri' => (string) $psrRequest->getUri(),
            'headers' => $headers,
            //'body' => (string) $psrRequest->getBody(),
            'body' => (string) $psrRequest->getBody(),
            '(body as JSON array)' => json_decode((string) $psrRequest->getBody()),
        ], 'INSTRUCTOR REQUEST (' . ($label ?? '') . ') ->');
    }

    public static function responseDebugger(
        Response $response,
        ResponseInterface $psrResponse,
        string $body = ''
    ): void {
        $headers = self::getResponseHeaders($psrResponse);

        $className = explode('\\', $response->getRequest()::class);
        $label = end($className);

        VarDumper::dump([
            'status' => $response->status(),
            'headers' => $headers,
            'body' => $body,
            '(body as JSON array)' => json_decode($body),
        ], 'INSTRUCTOR RESPONSE (' . ($label ?? '') . ') ->');
    }

    public static function getRequestHeaders(RequestInterface $psrRequest) : array {
        $headers = [];
        foreach ($psrRequest->getHeaders() as $headerName => $value) {
            $headers[$headerName] = implode(';', $value);
        }
        return $headers;
    }

    public static function getResponseHeaders(ResponseInterface $psrResponse) : array {
        $headers = [];
        foreach ($psrResponse->getHeaders() as $headerName => $value) {
            $headers[$headerName] = implode(';', $value);
        }
        return $headers;
    }

    public static function getRequestData(RequestInterface $request): array {
        return [
            'method' => $request->getMethod(),
            'uri' => (string) $request->getUri(),
            'headers' => $request->getHeaders(),
            'body' => (string) $request->getBody(),
        ];
    }

    public static function getResponseData(ResponseInterface $response): array {
        return [
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => (string) $response->getBody(),
        ];
    }
}
