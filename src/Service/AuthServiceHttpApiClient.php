<?php

declare(strict_types=1);

namespace App\Service;

use App\Exception\InvalidResponseException;
use GuzzleHttp\Client;

class AuthServiceHttpApiClient implements AuthServiceInterface
{
    private readonly Client $httpClient;

    public function __construct(
        string $apiHost,
        string $apiKey,
    ) {
        $this->httpClient = new Client([
            'base_uri' => $apiHost,
            'headers' => [
                'X-Auth-Token' => $apiKey,
            ],
        ]);
    }
    public function createGameSession(int $gameId, array $userIds): int
    {
        $response = $this->httpClient->post('/api/v1/game-session', [
            'json' => [
                'gameId' => $gameId,
                'userIds' => $userIds,
            ],
        ]);

        $responseData = json_decode($response->getBody()->getContents());

        $gameSessionId = isset($responseData['gameSessionId']) ? (int)$responseData['gameSessionId'] : 0;

        if ($gameSessionId <= 0) {
            throw new InvalidResponseException();
        }

        return $gameSessionId;
    }
}
