<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Limited;

use Closure;
use React\EventLoop\LoopInterface;
use React\Promise\Promise;
use React\Promise\PromiseInterface;
use ReactParallel\Contracts\ClosedException;
use ReactParallel\Contracts\GroupInterface;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\Contracts\PoolInterface;
use ReactParallel\EventLoop\EventLoopBridge;
use ReactParallel\Pool\Infinite\Infinite;
use SplQueue;
use WyriHaximus\PoolInfo\Info;

use function count;
use function React\Promise\reject;

final class Limited implements PoolInterface
{
    private PoolInterface $pool;

    private int $threadCount;

    private int $idleRuntimes;

    /** @var SplQueue<callable> */
    private SplQueue $queue;

    private ?GroupInterface $group = null;

    private bool $closed = false;

    public static function create(LoopInterface $loop, EventLoopBridge $eventLoopBridge, int $threadCount): self
    {
        return new self(new Infinite($loop, $eventLoopBridge, 1), $threadCount);
    }

    public static function createWithPool(PoolInterface $pool, int $threadCount): self
    {
        return new self($pool, $threadCount);
    }

    private function __construct(PoolInterface $pool, int $threadCount)
    {
        $this->pool         = $pool;
        $this->threadCount  = $threadCount;
        $this->idleRuntimes = $threadCount;
        $this->queue        = new SplQueue();

        if (! ($this->pool instanceof LowLevelPoolInterface)) {
            return;
        }

        $this->group = $this->pool->acquireGroup();
    }

    /**
     * @param mixed[] $args
     */
    public function run(Closure $callable, array $args = []): PromiseInterface
    {
        if ($this->closed === true) {
            return reject(ClosedException::create());
        }

        return (new Promise(function (callable $resolve): void {
            if ($this->idleRuntimes === 0) {
                $this->queue->enqueue($resolve);

                return;
            }

            $resolve();
        }))->then(function () use ($callable, $args): PromiseInterface {
            $this->idleRuntimes--;

            /** @psalm-suppress UndefinedInterfaceMethod */
            return $this->pool->run($callable, $args)->always(function (): void {
                $this->idleRuntimes++;
                $this->progressQueue();
            });
        });
    }

    public function close(): bool
    {
        $this->closed = true;

        if ($this->pool instanceof LowLevelPoolInterface && $this->group instanceof GroupInterface) {
            $this->pool->releaseGroup($this->group);
        }

         $this->pool->close();

        return true;
    }

    public function kill(): bool
    {
        $this->closed = true;

        if ($this->pool instanceof LowLevelPoolInterface && $this->group instanceof GroupInterface) {
            $this->pool->releaseGroup($this->group);
        }

        $this->pool->kill();

        return true;
    }

    /**
     * @return iterable<string, int>
     */
    public function info(): iterable
    {
        yield Info::TOTAL => $this->threadCount;
        yield Info::BUSY => $this->threadCount - $this->idleRuntimes;
        yield Info::CALLS => $this->queue->count();
        yield Info::IDLE  => $this->idleRuntimes;
        yield Info::SIZE  => $this->threadCount;
    }

    private function progressQueue(): void
    {
        if (count($this->queue) === 0) {
            return;
        }

        ($this->queue->dequeue())();
    }
}
