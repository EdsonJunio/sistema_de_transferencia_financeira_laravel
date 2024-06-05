<?php

namespace App\Services;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;

class MockyService
{
    private Client $client;

    public function __construct()
    {
        $this->client = new Client([
            'base_uri' => 'https://util.devi.tools/api'
        ]);
    }

    public function authorizetTransaction(): array
    {
        return $this->makeRequest('GET', '/v2/authorize', 'No authorization');
    }

    public function notifyUser(): array
    {
        return $this->makeRequest('GET', '/v1/notify', 'Error notifying user');
    }

    private function makeRequest(string $method, string $uri, string $defaultErrorMessage): array
    {
        try {
            $response = $this->client->request($method, $uri);
            return json_decode($response->getBody(), true);
        } catch (GuzzleException $exception) {
            return ['message' => $defaultErrorMessage];
        }
    }
}
