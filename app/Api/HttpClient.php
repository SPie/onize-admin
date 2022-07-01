<?php

namespace App\Api;

use App\Api\Exceptions\ApiException;
use App\Api\Exceptions\AuthenticationException;
use App\Api\Exceptions\AuthorizationException;
use App\Api\Exceptions\ClientException;
use App\Api\Exceptions\ServerException;
use App\Api\Exceptions\ValidationException;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    private const METHOD_GET  = 'GET';
    private const METHOD_POST = 'POST';
    private const METHOD_PATCH = 'PATCH';

    public function __construct(private readonly ClientInterface $guzzleClient)
    {}

    public function get(string $path, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->request(self::METHOD_GET, $path, $data, $headers);
    }

    public function post(string $path, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->request(self::METHOD_POST, $path, $data, $headers);
    }

    public function patch(string $path, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->request(self::METHOD_PATCH, $path, $data, $headers);
    }

    private function request(string $method, string $path, array $data = [], array $headers = []): ResponseInterface
    {
        try {
            return $this->guzzleClient->request(
                $method,
                $path,
                $this->addDataToOptions($method, ['headers' => $headers], $data)
            );
        } catch (GuzzleClientException $e) {
            throw $this->handleClientException($e);
        } catch (GuzzleServerException $e) {
            throw $this->handleServerException($e);
        }
    }

    private function addDataToOptions(string $method, array $options, array $data): array
    {
        if ($method === self::METHOD_GET) {
            $options['query'] = $data;

            return $options;
        }

        $options['json'] = $data;

        return $options;
    }

    private function handleClientException(GuzzleClientException $e): ApiException
    {
        $response = $e->getResponse();

        return match ($response->getStatusCode()) {
            401 => new AuthenticationException(),
            403 => new AuthorizationException(),
            422 => new ValidationException($this->getValidationErrors($response)),
            default => new ClientException()
        };
    }

    private function getValidationErrors(ResponseInterface $response): array
    {
        $body = \json_decode($response->getBody(), true);

        return $body['errors'] ?? [];
    }

    private function handleServerException(GuzzleServerException $e): ServerException
    {
        $body = \json_decode($e->getResponse()->getBody(), true);

        return new ServerException($body['message'] ?? '');
    }
}