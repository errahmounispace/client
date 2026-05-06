<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Foundation\Http\Events\RequestHandled;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class RequestHandledListener
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(RequestHandled $event): void
    {
        try {
            $this->laraowl->stage(ExecutionStage::Sending);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
