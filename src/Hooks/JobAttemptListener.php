<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class JobAttemptListener
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(JobProcessed|JobReleasedAfterException|JobFailed $event): void
    {
        try {
            $this->laraowl->jobAttempt($event);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
