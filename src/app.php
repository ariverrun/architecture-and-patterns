<?php

declare(strict_types=1);

$autoLoaderFilePath = __DIR__ . '/../vendor/autoload.php';

require_once $autoLoaderFilePath;

use App\Async\Runtime;
use App\Async\Async;
use App\Command\CallbackCommand;
use App\Command\AsyncQueueHandlingCommand;
use App\Command\HardStopQueueCommand;
use App\Command\DumpVarsCommand;
use App\Command\SoftStopQueueCommand;
use App\CommandQueue\CommandQueue;
use App\CommandQueue\CommandQueueCoroutine;
use App\CommandQueue\LoopQueueStrategy;
use App\CommandExceptionHandler\CommandExceptionHandler;

$dumpCallback = function (string $text): void {
    var_dump($text);
};

$exceptionHandler = new CommandExceptionHandler();
$handlerStrategyA = new LoopQueueStrategy($exceptionHandler);
$handlerStrategyB = new LoopQueueStrategy($exceptionHandler);

$queueA = new CommandQueue();
$queueB = new CommandQueue();

$coroutineA = new CommandQueueCoroutine('threadA', $queueA, $handlerStrategyA, $exceptionHandler);
$coroutineB = new CommandQueueCoroutine('threadB', $queueB, $handlerStrategyB, $exceptionHandler);

$exampleData = [
    'threadA' => [
        'queue' => $queueA,
        'coroutine' => $coroutineA,
        'commands' => [
            new CallbackCommand($dumpCallback, ['aaa1']),
            new CallbackCommand($dumpCallback, ['bbb1']),
            new CallbackCommand(function (int $sec): void {
                sleep($sec);
            }, [1]),
            // new SoftStopQueueCommand($coroutineA),
            new CallbackCommand($dumpCallback, ['ccc1']),
        ],
    ],
    'threadB' => [
        'queue' => $queueB,
        'coroutine' => $coroutineB,
        'commands' => [
            new CallbackCommand($dumpCallback, ['ddd2']),
            new CallbackCommand($dumpCallback, ['eee2']),
            new HardStopQueueCommand($coroutineB),
            new CallbackCommand($dumpCallback, ['fff2']),
        ],
    ],
];

$commandsToRun = [];

foreach ($exampleData as $thread => $threadExampleData) {
    $queue = $threadExampleData['queue'];
    $threadCommands = $threadExampleData['commands'];

    foreach ($threadCommands as $command) {
        $queue->enqueue($command);
    }

    $coroutine = $threadExampleData['coroutine'];

    $command = new AsyncQueueHandlingCommand($coroutine);

    $commandsToRun[] = $command;
}

(new Runtime(
    function () use ($commandsToRun, $queueA): void {

        foreach ($commandsToRun as $command) {
            $command->execute();
        }

        Async::sleep(1);

        $queueA->enqueue(new DumpVarsCommand(['in the end']));

        var_dump('AFTER');

        Async::sleep(1);

        var_dump('NEAR THE END');

        $queueA->enqueue(new DumpVarsCommand(['in the end again']));

        // var_dump($queueA);
    }
))();
