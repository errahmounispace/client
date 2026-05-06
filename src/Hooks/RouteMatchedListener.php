<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Routing\Events\RouteMatched;
use Laraowl\Client\Core;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class RouteMatchedListener
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(RouteMatched $event): void
    {
        try {
            $this->laraowl->attachMiddlewareToRoute($event->route);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
