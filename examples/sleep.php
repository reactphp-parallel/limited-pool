<?php

declare(strict_types=1);

use React\EventLoop\Loop;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;

use function React\Async\async;
use function React\Async\await;
use function React\Promise\all;
use function WyriHaximus\iteratorOrArrayToArray;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$finite = new Limited(new Infinite(new EventLoopBridge(), 1), 100);

$timer = Loop::addPeriodicTimer(1, function () use ($finite) {
    var_export(iteratorOrArrayToArray($finite->info()));
});

$promises = [];
foreach (range(0, 250) as $i) {
    $promises[] = async(static function (Limited $finite, int $i): int {
        $sleep = $finite->run(static function (int $sleep): int {
            sleep($sleep);

            return $sleep;
        }, [random_int(1, 13)]);

        echo $i, '; ', $sleep, PHP_EOL;

        return $sleep;
    })($finite, $i);
}

$signalHandler = static function () use ($finite): void {
    $finite->close();
    Loop::stop();
};

Loop::addSignal(SIGINT, $signalHandler);

await(all($promises));

$finite->close();
Loop::removeSignal(SIGINT, $signalHandler);
Loop::cancelTimer($timer);
