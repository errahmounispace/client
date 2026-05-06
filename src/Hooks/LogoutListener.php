<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Auth\Events\Logout;
use Laraowl\Client\Core;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class LogoutListener
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Logout $event): void
    {
        try {
            if ($event->user !== null) {
                $this->laraowl->remember($event->user);
            }
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
