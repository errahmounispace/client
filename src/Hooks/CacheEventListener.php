<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Cache\Events\CacheEvent;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class CacheEventListener
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(CacheEvent $event): void
    {
        try {
            $this->laraowl->cacheEvent($event);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
