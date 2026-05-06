<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Database\Events\QueryExecuted;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class QueryExecutedListener
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(QueryExecuted $event): void
    {
        try {
            $this->laraowl->query($event);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
