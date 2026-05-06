<?php

namespace Laraowl\Client\Hooks;

use Carbon\Carbon;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\CommandState;
use Symfony\Component\Console\Input\InputInterface;
use Throwable;

/**
 * @internal
 */
final class CommandLifecycleIsLongerThanHandler
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Carbon $startedAt, InputInterface $input, int $status): void
    {
        try {
            $this->laraowl->stage(ExecutionStage::End);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        try {
            $this->laraowl->command($input, $status);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        $this->laraowl->finishExecution();
    }
}
