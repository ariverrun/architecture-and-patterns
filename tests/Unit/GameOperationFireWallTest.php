<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\DependencyInjection\IoC;
use App\Consumer\GameOperationMessageDto;
use App\Firewall\GameOperationFirewall;
use App\Repository\GameRepositoryInterface;
use App\Service\JWTDecoderInterface;
use PhpAmqpLib\Message\AMQPMessage;
use PHPUnit\Framework\TestCase;
use Tests\Traits\IocSetupTrait;
use Exception;

class GameOperationFireWallTest extends TestCase
{
    use IocSetupTrait;

    public function setUp(): void
    {
        $this->setUpIocDependencyResolver();
    }

    public function tearDown(): void
    {
        $this->setUpIocDependencyResolver();
    }

    public function testSuccessfulPassing(): void
    {
        $jwtDecoder = $this->createMock(JWTDecoderInterface::class);

        $jwtDecoder->expects($this->once())
                        ->method('decode')
                        ->willReturn([
                            'gameSessionId' => 100,
                        ]);

        IoC::resolve('Ioc.Register', 'Jwt.Decoder', static function () use ($jwtDecoder): JWTDecoderInterface {
            return $jwtDecoder;
        })();

        $gameRepository = $this->createMock(GameRepositoryInterface::class);

        $gameRepository->expects($this->once())
                        ->method('getSessionIdByGameId')
                        ->willReturn(100);

        IoC::resolve('Ioc.Register', 'Repository.Game', static function () use ($gameRepository): GameRepositoryInterface {
            return $gameRepository;
        })();

        $fireWall = new GameOperationFirewall();

        $accessToken = 'vdfvdfdf';

        $amqpMessageMock = $this->createMock(AMQPMessage::class);

        $amqpMessageMock->expects($this->once())
                        ->method('get_properties')
                        ->willReturn([
                            'application_headers' => [
                                'Authorization' => 'Bearer ' . $accessToken,
                            ],
                        ]);

        $dto = new GameOperationMessageDto(1, '1', '1', null);

        $isAccessGranted = $fireWall->isAccessGranted($amqpMessageMock, $dto);

        $this->assertTrue($isAccessGranted);
    }

    public function testWrongSessionId(): void
    {
        $jwtDecoder = $this->createMock(JWTDecoderInterface::class);

        $jwtDecoder->expects($this->once())
                        ->method('decode')
                        ->willReturn([
                            'gameSessionId' => 101,
                        ]);

        IoC::resolve('Ioc.Register', 'Jwt.Decoder', static function () use ($jwtDecoder): JWTDecoderInterface {
            return $jwtDecoder;
        })();

        $gameRepository = $this->createMock(GameRepositoryInterface::class);

        $gameRepository->expects($this->once())
                        ->method('getSessionIdByGameId')
                        ->willReturn(100);

        IoC::resolve('Ioc.Register', 'Repository.Game', static function () use ($gameRepository): GameRepositoryInterface {
            return $gameRepository;
        })();

        $fireWall = new GameOperationFirewall();

        $accessToken = 'vdfvdgbfg';

        $amqpMessageMock = $this->createMock(AMQPMessage::class);

        $amqpMessageMock->expects($this->once())
                        ->method('get_properties')
                        ->willReturn([
                            'application_headers' => [
                                'Authorization' => 'Bearer ' . $accessToken,
                            ],
                        ]);

        $dto = new GameOperationMessageDto(1, '1', '1', null);

        $isAccessGranted = $fireWall->isAccessGranted($amqpMessageMock, $dto);

        $this->assertFalse($isAccessGranted);
    }

    public function testExceptionOnJWTDecoding(): void
    {
        $jwtDecoder = $this->createMock(JWTDecoderInterface::class);

        $jwtDecoder->expects($this->once())
                        ->method('decode')
                        ->willThrowException(new Exception());

        IoC::resolve('Ioc.Register', 'Jwt.Decoder', static function () use ($jwtDecoder): JWTDecoderInterface {
            return $jwtDecoder;
        })();

        $gameRepository = $this->createMock(GameRepositoryInterface::class);

        IoC::resolve('Ioc.Register', 'Repository.Game', static function () use ($gameRepository): GameRepositoryInterface {
            return $gameRepository;
        })();

        $fireWall = new GameOperationFirewall();

        $accessToken = 'vdfvdfdftdr';

        $amqpMessageMock = $this->createMock(AMQPMessage::class);

        $amqpMessageMock->expects($this->any())
                        ->method('get_properties')
                        ->willReturn([
                            'application_headers' => [
                                'Authorization' => 'Bearer ' . $accessToken,
                            ],
                        ]);

        $dto = new GameOperationMessageDto(1, '1', '1', null);

        $isAccessGranted = $fireWall->isAccessGranted($amqpMessageMock, $dto);

        $this->assertFalse($isAccessGranted);
    }

    public function testNoAuthHeader(): void
    {
        $jwtDecoder = $this->createMock(JWTDecoderInterface::class);

        IoC::resolve('Ioc.Register', 'Jwt.Decoder', static function () use ($jwtDecoder): JWTDecoderInterface {
            return $jwtDecoder;
        })();

        $gameRepository = $this->createMock(GameRepositoryInterface::class);

        IoC::resolve('Ioc.Register', 'Repository.Game', static function () use ($gameRepository): GameRepositoryInterface {
            return $gameRepository;
        })();

        $fireWall = new GameOperationFirewall();

        $amqpMessageMock = $this->createMock(AMQPMessage::class);

        $amqpMessageMock->expects($this->once())
                        ->method('get_properties')
                        ->willReturn([
                            'application_headers' => [
                            ],
                        ]);

        $dto = new GameOperationMessageDto(1, '1', '1', null);

        $isAccessGranted = $fireWall->isAccessGranted($amqpMessageMock, $dto);

        $this->assertFalse($isAccessGranted);
    }
}
