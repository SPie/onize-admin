<?php

namespace Tests\Helpers;

use App\Api\Exceptions\ValidationException;
use App\Api\HttpClient;
use App\Api\ApiClient;
use GuzzleHttp\ClientInterface;
use Mockery as m;
use Mockery\CompositeExpectation;
use Mockery\MockInterface;
use Psr\Http\Message\ResponseInterface;

trait ApiHelper
{
    /**
     * @return HttpClient|MockInterface
     */
    private function createHttpClient(): HttpClient
    {
        return m::spy(HttpClient::class);
    }

    private function mockHttpClientGet(MockInterface $httpClient, $response, string $path, array $data, array $headers): CompositeExpectation
    {
        return $httpClient
            ->shouldReceive('get')
            ->with($path, $data, $headers)
            ->andThrow($response);
    }

    /**
     * @param ResponseInterface|\Exception $response
     */
    private function mockHttpClientPost(MockInterface $httpClient, $response, string $path, array $data): CompositeExpectation
    {
        return $expectation = $httpClient
            ->shouldReceive('post')
            ->with($path, $data)
            ->andThrow($response);
    }

    /**
     * @return ClientInterface|MockInterface
     */
    private function createGuzzleClient(): ClientInterface
    {
        return m::spy(ClientInterface::class);
    }

    private function mockGuzzleClientRequest(
        MockInterface $guzzleClient,
        $response,
        string $method,
        string $uri,
        array $options
    ): CompositeExpectation {
        return $guzzleClient
            ->shouldReceive('request')
            ->with($method, $uri, $options)
            ->andThrow($response);
    }

    /**
     * @return ResponseInterface|MockInterface
     */
    private function createResponse(): ResponseInterface
    {
        return m::spy(ResponseInterface::class);
    }

    private function mockResponseGetStatusCode(MockInterface $response, int $statusCode): CompositeExpectation
    {
        return $response
            ->shouldReceive('getStatusCode')
            ->andReturn($statusCode);
    }

    private function mockResponseGetHeader(MockInterface $response, array $header, string $name): CompositeExpectation
    {
        return $response
            ->shouldReceive('getHeader')
            ->with($name)
            ->andReturn($header);
    }

    private function mockResponseGetBody(MockInterface $response, string $body): CompositeExpectation
    {
        return $response
            ->shouldReceive('getBody')
            ->andReturn($body);
    }

    /**
     * @return ApiClient|MockInterface
     */
    private function createApiClient(): ApiClient
    {
        return m::spy(ApiClient::class);
    }

    private function mockApiClientRegister(
        MockInterface $onizeApiClient,
        $response,
        string $email,
        string $password
    ): CompositeExpectation {
        $expectation = $onizeApiClient
            ->shouldReceive('register')
            ->with($email, $password);

        if ($response instanceof \Exception) {
            return $expectation->andThrow($response);
        }

        return $expectation->andReturn($response);
    }

    private function mockApiClientAuthenticatedUser(MockInterface $apiClient, $response): CompositeExpectation
    {
        $expectation = $apiClient->shouldReceive('authenticatedUser');

        if ($response instanceof \Exception) {
            return $expectation->andThrow($response);
        }

        return $expectation->andReturn($response);
    }

    /**
     * @return ValidationException|MockInterface
     */
    private function createValidationException(): ValidationException
    {
        return m::spy(ValidationException::class);
    }
}