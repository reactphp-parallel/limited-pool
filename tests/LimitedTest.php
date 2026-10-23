<?php

// phpcs:disable
declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Limited;

use PHPUnit\Framework\Attributes\Test;
use ReactParallel\Contracts\PoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use ReactParallel\Tests\AbstractPoolTest;
use WyriHaximus\AsyncTestUtilities\TimeOut;
use WyriHaximus\PoolInfo\Info;
use WyriHaximus\PoolInfo\PoolInfoInterface;
use WyriHaximus\PoolInfo\PoolInfoTestTrait;

#[TimeOut(13)]
final class LimitedTest extends AbstractPoolTest
{
    use LimitedTestTrait;
    use PoolInfoTestTrait;

    /** @phpstan-ignore method.unused */
    private function poolFactory(): PoolInfoInterface
    {
        return new Limited(new Infinite(new EventLoopBridge(), 1), 5);
    }

    protected function createPool(): PoolInterface
    {
        return new Limited(new Infinite(new EventLoopBridge(), 1), 5);
    }

    #[Test]
    public function info(): void
    {
        self::assertSame([
            Info::TOTAL => 5,
            Info::BUSY => 0,
            Info::CALLS => 0,
            Info::IDLE => 5,
            Info::SIZE => 5,
        ], [...new Limited(new Infinite(new EventLoopBridge(), 1), 5)->info()]);
    }
}
