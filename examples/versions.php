<?php


use PackageVersions\Versions;
use React\EventLoop\Factory;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Limited\Limited;
use WyriHaximus\React\Parallel\Finite;
use function WyriHaximus\iteratorOrArrayToArray;

require dirname(__DIR__) . DIRECTORY_SEPARATOR . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php';

$loop = Factory::create();

$finite = Limited::create($loop, new EventLoopBridge($loop), 2);

$timer = $loop->addPeriodicTimer(1, function () use ($finite) {
    var_export(iteratorOrArrayToArray($finite->info()));
});
$finite->run(function (): array {
    return Versions::VERSIONS;
})->then(function ($versions) use ($finite, $loop, $timer): void {
    var_export($versions);

    $finite->close();
    $loop->cancelTimer($timer);
    $loop->stop();
})->done();

echo 'Loop::run()', PHP_EOL;
$loop->run();
echo 'Loop::done()', PHP_EOL;