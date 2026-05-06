<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Laraowl\Client\Compatibility;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class ScheduledTaskListener
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(ScheduledTaskFinished|ScheduledTaskSkipped|ScheduledTaskFailed $event): void
    {
        // We report the exception here because the scheduler handles it after the task has finished and the data is ingested.
        // This ensures that the exception is captured in the scheduled task record.
        if ($event instanceof ScheduledTaskFailed) {
            $this->laraowl->report($event->exception);
        }

        if ($this->isFinishedEventForFailedTask($event)) {
            return;
        }

        if ($event instanceof ScheduledTaskSkipped) {
            $this->laraowl->prepareForScheduledTask($event->task);
        }

        try {
            $this->laraowl->scheduledTask($event);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        $this->laraowl->finishExecution()->waitForExecution();
    }

    private function isFinishedEventForFailedTask(ScheduledTaskFinished|ScheduledTaskSkipped|ScheduledTaskFailed $event): bool
    {
        return Compatibility::$firesFinishedAndFailedEventsForScheduledConsoleCommands &&
            $event instanceof ScheduledTaskFinished &&
            $event->task->command !== null &&
            $event->task->exitCode !== 0 &&
            ! $event->task->runInBackground;
    }
}
