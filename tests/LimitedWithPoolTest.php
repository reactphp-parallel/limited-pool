<?php

// phpcs:disable
declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Limited;

use ReactParallel\Contracts\PoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use ReactParallel\Tests\AbstractPoolTest;
use WyriHaximus\AsyncTestUtilities\TimeOut;
//use WyriHaximus\PoolInfo\PoolInfoInterface;
//use WyriHaximus\PoolInfo\PoolInfoTestTrait;

#[TimeOut(13)]
final class LimitedWithPoolTest extends AbstractPoolTest
{
    use LimitedTestTrait;
//    use PoolInfoTestTrait;
//
//    private function poolFactory(): PoolInfoInterface
//    {
//        return new Limited(new Infinite(new EventLoopBridge(), 1), 5);
//    }

    protected function createPool(): PoolInterface
    {
        return new Limited(new Infinite(new EventLoopBridge(), 1), 5);
    }
}
