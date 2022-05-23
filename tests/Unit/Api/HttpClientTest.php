<?php

namespace Tests\Unit\Api;

use App\Api\Exceptions\AuthenticationException;
use App\Api\Exceptions\AuthorizationException;
use App\Api\Exceptions\ClientException;
use App\Api\Exceptions\ServerException;
use App\Api\Exceptions\ValidationException;
use App\Api\HttpClient;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\ClientException as GuzzleClientException;
use GuzzleHttp\Exception\ServerException as GuzzleServerException;
use Tests\Helpers\ApiHelper;
use Tests\TestCase;

class HttpClientTest extends TestCase
{
    use ApiHelper;

    private function getHttpClient(ClientInterface $guzzle = null): HttpClient
    {
        return new HttpClient($guzzle ?: $this->createGuzzleClient());
    }

    private function setUpPostTest(
        bool $withClientException = false,
        bool $withAuthenticationException = false,
        bool $withAuthorizationException = false,
        bool $withValidationException = false,
        bool $withErrors = true,
        bool $withOtherClientException = false,
        bool $withServerException = false
    ): array {
        $path = $this->getFaker()->word;
        $data = [$this->getFaker()->word => $this->getFaker()->word];
        $headers = [$this->getFaker()->word => $this->getFaker()->word];
        $status = 201;
        if ($withClientException) {
            $status = 400;
        }
        if ($withAuthenticationException) {
            $status = 401;
        }
        if ($withAuthorizationException) {
            $status = 403;
        }
        if ($withValidationException) {
            $status = 422;
        }
        if ($withOtherClientException) {
            $status = 418;
        }
        if ($withServerException) {
            $status = 500;
        }
        $errorMessage = $this->getFaker()->word;
        $errors = [$this->getFaker()->word => $this->getFaker()->word];
        $response = $this->createResponse();
        $this->mockResponseGetStatusCode($response, $status);
        $this->mockResponseGetBody($response, \json_encode($withErrors ? ['errors' => $errors, 'message' => $errorMessage] : []));
        $exception = new GuzzleClientException(
            $this->getFaker()->word,
            $this->createRequest(),
            $response
        );
        if ($withServerException) {
            $exception = new GuzzleServerException(
                $this->getFaker()->word,
                $this->createRequest(),
                $response
            );
        }
        $guzzle = $this->createGuzzleClient();
        $this->mockGuzzleClientRequest(
            $guzzle,
            (
                $withServerException
                || $withOtherClientException
                || $withValidationException
                || $withClientException
                || $withAuthenticationException
                || $withAuthorizationException
            )
                ? $exception
                : $response,
            'POST',
            $path,
            ['json' => $data, 'headers' => $headers]
        );
        $httpClient = $this->getHttpClient($guzzle);

        return [$httpClient, $path, $data, $headers, $response, $errors, $errorMessage];
    }

    public function testPost(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers, $response] = $this->setUpPostTest();

        $this->assertEquals($response, $httpClient->post($path, $data, $headers));
    }

    public function testPostWithClientException(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers] = $this->setUpPostTest(withClientException: true);

        $this->expectException(ClientException::class);

        $httpClient->post($path, $data, $headers);
    }

    public function testPostWithAuthenticationException(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers] = $this->setUpPostTest(withAuthenticationException: true);

        $this->expectException(AuthenticationException::class);

        $httpClient->post($path, $data, $headers);
    }

    public function testPostWithAuthorizationException(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers] = $this->setUpPostTest(withAuthorizationException: true);

        $this->expectException(AuthorizationException::class);

        $httpClient->post($path, $data, $headers);
    }

    public function testPostWithValidationException(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers, $response, $errors] = $this->setUpPostTest(withValidationException: true);

        try {
            $httpClient->post($path, $data, $headers);

            $this->fail('ValidationException should be thrown');
        } catch (ValidationException $e) {
            $this->assertEquals($errors, $e->getErrors());
        }
    }

    public function testPostWithValidationExceptionWithoutErrors(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers] = $this->setUpPostTest(withValidationException: true, withErrors: false);

        try {
            $httpClient->post($path, $data, $headers);

            $this->fail('ValidationException should be thrown');
        } catch (ValidationException $e) {
            $this->assertEmpty($e->getErrors());
        }
    }

    public function testPostWithOtherClientException(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers] = $this->setUpPostTest(withOtherClientException: true);

        $this->expectException(ClientException::class);

        $httpClient->post($path, $data, $headers);
    }

    public function testPostWithServerException(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers, $response, $errors, $errorMessage] = $this->setUpPostTest(withServerException: true);

        try {
            $httpClient->post($path, $data, $headers);

            $this->fail('ServerException should be thrown');
        } catch (ServerException $e) {
            $this->assertEquals($errorMessage, $e->getMessage());
        }
    }

    private function setUpGetTest(): array
    {
        $path = $this->getFaker()->word;
        $data = [$this->getFaker()->word => $this->getFaker()->word];
        $headers = [$this->getFaker()->word => $this->getFaker()->word];
        $response = $this->createResponse();
        $guzzle = $this->createGuzzleClient();
        $this->mockGuzzleClientRequest($guzzle, $response, 'GET', $path, ['query' => $data, 'headers' => $headers]);
        $httpClient = $this->getHttpClient($guzzle);

        return [$httpClient, $path, $data, $headers, $response];
    }

    public function testGet(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers, $response] = $this->setUpGetTest();

        $this->assertEquals($response, $httpClient->get($path, $data, $headers));
    }
}