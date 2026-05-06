<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Foundation\Events\Terminating;
use Laraowl\Client\Compatibility;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class TerminatingListener
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Terminating $event): void
    {
        if (! Compatibility::$terminatingEventExists) {
            return;
        }

        try {
            $this->laraowl->stage(ExecutionStage::Terminating);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
