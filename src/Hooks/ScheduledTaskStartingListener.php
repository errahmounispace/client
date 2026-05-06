<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Console\Events\ScheduledTaskStarting;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class ScheduledTaskStartingListener
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(ScheduledTaskStarting $event): void
    {
        try {
            $this->laraowl->prepareForScheduledTask($event->task);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
