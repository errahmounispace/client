<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Routing\Events\ResponsePrepared;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class ResponsePreparedListener
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(ResponsePrepared $event): void
    {
        try {
            if ($this->laraowl->executionStageIs(ExecutionStage::Render)) {
                $this->laraowl->stage(ExecutionStage::AfterMiddleware);
            }
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
