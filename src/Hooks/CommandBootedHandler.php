<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Contracts\Foundation\Application;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class CommandBootedHandler
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Application $app): void
    {
        try {
            $this->laraowl->stage(ExecutionStage::Action);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
