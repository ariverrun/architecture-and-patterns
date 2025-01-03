<?php

declare(strict_types=1);

namespace App\Firewall;

use App\AMQP\Firewall\FirewallInterface;
use App\DependencyInjection\IoC;
use App\Consumer\GameOperationMessageDto;
use App\Exception\AccessDeniedException;
use App\Service\JWTDecoderInterface;
use PhpAmqpLib\Message\AMQPMessage;

class GameOperationFirewall implements FirewallInterface
{
    private readonly JWTDecoderInterface $jwtDecoder;

    public function __construct()
    {
        $this->jwtDecoder = IoC::resolve('Jwt.Decoder');
    }

    /**
     * @param GameOperationMessageDto $consumerArg
     */
    public function isAccessGranted(AMQPMessage $aMQPMessage, mixed $consumerArg): bool
    {
        $headers = $aMQPMessage->get_properties()['application_headers'] ?? [];

        $authorizationHeader = $headers['Authorization'] ?? null;

        $accessToken = str_replace('Bearer ', '', $authorizationHeader);

        $payload = $this->jwtDecoder->decode($accessToken);

        $gameSessionId = isset($payload['gameSessionId']) ? (int)$payload['gameSessionId'] : 0;

        if ($gameSessionId !== $consumerArg->gameId) {
            throw new AccessDeniedException();
        }

        return true;
    }
}
