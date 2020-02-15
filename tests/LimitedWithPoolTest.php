<?php declare(strict_types=1);

namespace ReactParallel\Tests\Pool\Limited;

use React\EventLoop\Factory;
use React\EventLoop\LoopInterface;
use ReactParallel\Contracts\PoolInterface;
use ReactParallel\Pool\Infinite\Infinite;
use ReactParallel\Pool\Limited\Limited;
use ReactParallel\Tests\AbstractPoolTest;
use WyriHaximus\PoolInfo\PoolInfoInterface;
use WyriHaximus\PoolInfo\PoolInfoTestTrait;

/**
 * @internal
 */
final class LimitedWithPoolTest extends AbstractPoolTest
{
    use PoolInfoTestTrait;

    private function poolFactory(): PoolInfoInterface
    {
        return Limited::createWithPool(new Infinite(Factory::create(), 0.2), 5);
    }

    protected function createPool(LoopInterface $loop): PoolInterface
    {
        return Limited::createWithPool(new Infinite($loop, 0.2), 5);
    }
}
