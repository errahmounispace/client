<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Console\Events\CommandFinished;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Console\Events\ScheduledTaskFailed;
use Illuminate\Console\Events\ScheduledTaskFinished;
use Illuminate\Console\Events\ScheduledTaskSkipped;
use Illuminate\Console\Events\ScheduledTaskStarting;
use Illuminate\Contracts\Console\Kernel as ConsoleKernelContract;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Queue\Events\JobFailed;
use Illuminate\Queue\Events\JobPopping;
use Illuminate\Queue\Events\JobProcessed;
use Illuminate\Queue\Events\JobProcessing;
use Illuminate\Queue\Events\JobReleasedAfterException;
use Illuminate\Queue\Events\Looping;
use Illuminate\Queue\Events\WorkerStopping;
use Laraowl\Client\Core;
use Laraowl\Client\Facades\LaraowlClient;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class CommandStartingListener
{
    private bool $hasRun = false;

    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Dispatcher $events,
        private Core $laraowl,
        private ConsoleKernelContract $kernel,
    ) {
        //
    }

    public function __invoke(CommandStarting $event): void
    {
        if ($this->hasRun) {
            return;
        }

        $this->hasRun = true;

        try {
            match ($event->command) {
                'queue:work', 'queue:listen', 'horizon:work', 'vapor:work' => $this->registerJobHooks($event),
                'schedule:run', 'schedule:work' => $this->registerScheduledTaskHooks(),
                'help', 'inspire', 'schedule:finish' => null,
                default => $this->registerCommandHooks($event),
            };
        } catch (Throwable $e) {
            LaraowlClient::unrecoverableExceptionOccurred($e);
        }
    }

    private function registerJobHooks(CommandStarting $event): void
    {
        $this->laraowl->configureForJobs();

        /**
         * @see \Laraowl\Client\Core::finishExecution()
         * @see \Laraowl\Client\State\CommandState::flush()
         * @see \Laraowl\Client\State\CommandState::$timestamp
         * @see \Laraowl\Client\State\CommandState::$id
         */
        $this->events->listen([
            Looping::class,
            JobPopping::class,
            JobProcessing::class,
            WorkerStopping::class,
            CommandFinished::class,
        ], (new WorkerLifecycleListener($this->laraowl))(...));

        /**
         * @see \Laraowl\Client\Records\JobAttempt
         * @see \Laraowl\Client\Core::finishExecution()
         */
        $this->events->listen([
            JobProcessed::class,
            JobReleasedAfterException::class,
            JobFailed::class,
        ], (new JobAttemptListener($this->laraowl))(...));

        if ($event->command === 'vapor:work') {
            $this->events->listen(CommandFinished::class, (new VaporWorkCommandFinishedListener($this->laraowl))(...));
        }
    }

    private function registerScheduledTaskHooks(): void
    {
        $this->laraowl->configureForScheduledTasks();

        $this->events->listen(ScheduledTaskStarting::class, (new ScheduledTaskStartingListener($this->laraowl))(...));

        /**
         * @see \Laraowl\Client\Core::finishExecution()
         */
        $this->events->listen([
            ScheduledTaskFinished::class,
            ScheduledTaskSkipped::class,
            ScheduledTaskFailed::class,
        ], (new ScheduledTaskListener($this->laraowl))(...));
    }

    private function registerCommandHooks(CommandStarting $event): void
    {
        if (! $this->kernel instanceof ConsoleKernel) {
            return;
        }

        $this->laraowl->configureCommandSampling($event->command);

        $this->laraowl->prepareForCommand($event->command);

        /**
         * @see \Laraowl\Client\ExecutionStage::Terminating
         */
        $this->events->listen(CommandFinished::class, (new CommandFinishedListener($this->laraowl))(...));

        /**
         * @see \Laraowl\Client\ExecutionStage::End
         * @see \Laraowl\Client\Records\Command
         * @see \Laraowl\Client\Core::finishExecution()
         */
        $this->kernel->whenCommandLifecycleIsLongerThan(-1, new CommandLifecycleIsLongerThanHandler($this->laraowl));
    }
}
