<?php

namespace Tests\Unit\Api;

use App\Api\HttpClient;
use GuzzleHttp\ClientInterface;
use Tests\Helpers\ApiHelper;
use Tests\TestCase;

class HttpClientTest extends TestCase
{
    use ApiHelper;

    private function getHttpClient(ClientInterface $guzzle = null): HttpClient
    {
        return new HttpClient($guzzle ?: $this->createGuzzleClient());
    }

    private function setUpPostTest(bool $withInvalidResponse = false): array
    {
        $path = $this->getFaker()->word;
        $data = [$this->getFaker()->word => $this->getFaker()->word];
        $headers = [$this->getFaker()->word => $this->getFaker()->word];
        $response = $this->createResponse();
        $this->mockResponseGetStatusCode($response, 201);
        $guzzle = $this->createGuzzleClient();
        $this->mockGuzzleClientRequest($guzzle, $response, 'POST', $path, ['json' => $data, 'headers' => $headers]);
        $httpClient = $this->getHttpClient($guzzle);

        return [$httpClient, $path, $data, $headers, $response];
    }

    public function testPost(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers, $response] = $this->setUpPostTest();

        $this->assertEquals($response, $httpClient->post($path, $data, $headers));
    }

    public function testPostWithInvalidResponse(): void
    {
        /** @var HttpClient $httpClient */
        [$httpClient, $path, $data, $headers] = $this->setUpPostTest(withInvalidResponse: true);

        $httpClient->post($path, $data, $headers);
    }
}