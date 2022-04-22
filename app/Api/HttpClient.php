<?php

namespace App\Api;

use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;

class HttpClient
{
    public function __construct(private ClientInterface $guzzleClient)
    {}

    public function post(string $path, array $data = [], array $headers = []): ResponseInterface
    {
        return $this->guzzleClient->request('POST', $path, [
            'json'    => $data,
            'headers' => $headers,
        ]);
    }
}