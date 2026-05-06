<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Console\Events\CommandFinished;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;

/**
 * @internal
 */
final class VaporWorkCommandFinishedListener
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
        $this->laraowl->finishExecution();
    }
}
