<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Queue\Events\JobQueued;
use Illuminate\Queue\Events\JobQueueing;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class QueuedJobListener
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(JobQueueing|JobQueued $event): void
    {
        try {
            $this->laraowl->queuedJob($event);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
