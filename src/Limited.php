<?php

declare(strict_types=1);

namespace ReactParallel\Pool\Limited;

use Closure;
use React\Promise\Deferred;
use ReactParallel\Contracts\ClosedException;
use ReactParallel\Contracts\GroupInterface;
use ReactParallel\Contracts\LowLevelPoolInterface;
use ReactParallel\Contracts\PoolInterface;
use SplQueue;
use WyriHaximus\PoolInfo\Info;

use function count;
use function React\Async\await;

final class Limited implements PoolInterface
{
    private int $idleRuntimes;

    /** @var SplQueue<callable> */
    private SplQueue $queue;

    private GroupInterface|null $group = null;

    private bool $closed = false;

    public function __construct(private PoolInterface $pool, private int $threadCount)
    {
        $this->idleRuntimes = $threadCount;
        $this->queue        = new SplQueue();

        if (! ($this->pool instanceof LowLevelPoolInterface)) {
            return;
        }

        $this->group = $this->pool->acquireGroup();
    }

    /**
     * @param (Closure():T) $callable
     * @param array<mixed>  $args
     *
     * @return T
     *
     * @template T
     */
    public function run(Closure $callable, array $args = []): mixed
    {
        if ($this->closed === true) {
            throw ClosedException::create();
        }

        if ($this->idleRuntimes === 0) {
            $deferred = new Deferred();
            $this->queue->enqueue(static fn () => $deferred->resolve(null));

            await($deferred->promise());
        }

        try {
            $this->idleRuntimes--;

            return $this->pool->run($callable, $args);
        } finally {
            $this->idleRuntimes++;
            $this->progressQueue();
        }
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

    /** @return iterable<string, int> */
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
