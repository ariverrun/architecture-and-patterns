<?php

declare(strict_types=1);

namespace App\Firewall;

use App\AMQP\Consumer\ConsumingContext;
use App\AMQP\Firewall\FirewallInterface;
use App\DependencyInjection\IoC;
use App\Consumer\GameOperationMessageDto;
use App\Repository\GameRepositoryInterface;
use App\Service\JWTDecoderInterface;
use PhpAmqpLib\Message\AMQPMessage;
use Throwable;

class GameOperationFirewall implements FirewallInterface
{
    private readonly JWTDecoderInterface $jwtDecoder;
    private readonly GameRepositoryInterface $gameRepository;

    public function __construct()
    {
        $this->jwtDecoder = IoC::resolve('Jwt.Decoder');
        $this->gameRepository = IoC::resolve('Repository.Game');
    }

    /**
     * @param GameOperationMessageDto $consumerArg
     */
    public function isAccessGranted(AMQPMessage $aMQPMessage, mixed $consumerArg, ConsumingContext $context): bool
    {
        $headers = $aMQPMessage->get_properties()['application_headers'] ?? [];

        $authorizationHeader = $headers['Authorization'] ?? '';

        $accessToken = trim(str_replace('Bearer ', '', $authorizationHeader));

        if (!$accessToken) {
            return false;
        }

        try {
            $payload = $this->jwtDecoder->decode($accessToken);
        } catch (Throwable) {
            return false;
        }

        $gameSessionId = isset($payload['gameSessionId']) ? (int)$payload['gameSessionId'] : 0;

        if ($gameSessionId <= 0 || $gameSessionId !== $this->gameRepository->getSessionIdByGameId($consumerArg->gameId)) {
            return false;
        }

        $userId = isset($payload['userId']) ? (int)$payload['userId'] : 0;

        $context->setUserId($userId);

        return true;
    }
}
