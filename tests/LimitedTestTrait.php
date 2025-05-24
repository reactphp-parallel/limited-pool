<?php

declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Limited;

use PHPUnit\Framework\Attributes\Test;
use React\EventLoop\Loop;
use React\Promise\Deferred;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use Throwable;

use function range;
use function React\Async\async;
use function React\Async\await;
use function React\Promise\all;

trait LimitedTestTrait
{
    #[Test]
    public function assertWeCanRunMoreThanThePoolLimit(): void
    {
        $pool = new Limited(new Infinite(new EventLoopBridge(), 1), 5);

        $is = [];
        foreach (range(1, 1337) as $i) {
            $deferred = new Deferred();
            Loop::futureTick(async(static function () use ($pool, $deferred, $i): void {
                try {
                    $deferred->resolve($pool->run(static fn (): int => $i));
                } catch (Throwable $exception) {
                    $deferred->reject($exception);
                }
            }));
            $is[] = $deferred->promise();
        }

        self::assertSame(range(1, 1337), await(all($is)));
    }
}
