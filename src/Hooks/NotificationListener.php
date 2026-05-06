<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Notifications\Events\NotificationSending;
use Illuminate\Notifications\Events\NotificationSent;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class NotificationListener
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(NotificationSending|NotificationSent $event): void
    {
        try {
            $this->laraowl->notification($event);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
