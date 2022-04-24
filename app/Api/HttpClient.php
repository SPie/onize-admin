<?php

namespace App\Api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    public function __construct(private readonly ClientInterface $guzzleClient)
    {}

    public function get(string $path, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->guzzleClient->request('GET', $path, [
            'query'   => $data,
            'headers' => $headers,
        ]);
    }

    public function post(string $path, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->guzzleClient->request('POST', $path, [
            'json'    => $data,
            'headers' => $headers,
        ]);
    }
}