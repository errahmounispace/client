<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Queue\Events\JobPopping;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class WorkerLifecycleListener
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Looping|JobPopping|JobProcessing|WorkerStopping|CommandFinished $event): void
    {
        try {
            match ($event::class) {
                Looping::class, WorkerStopping::class => $this->laraowl->finishExecution()->waitForExecution(),
                CommandFinished::class => $event->command === 'queue:work' && $this->laraowl->finishExecution()->waitForExecution(),
                JobPopping::class => $this->laraowl->prepareForNextJob(),
                JobProcessing::class => $this->laraowl->prepareForJob($event->job),
            };
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
