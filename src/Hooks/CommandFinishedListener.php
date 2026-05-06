<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Console\Events\CommandFinished;
use Laraowl\Client\Compatibility;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class CommandFinishedListener
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(CommandFinished $event): void
    {
        try {
            if ($this->laraowl->capturingCommandNamed($event->command) && ! Compatibility::$terminatingEventExists) {
                $this->laraowl->stage(ExecutionStage::Terminating);
            }
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
