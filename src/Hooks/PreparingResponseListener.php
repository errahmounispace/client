<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Routing\Events\PreparingResponse;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class PreparingResponseListener
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(PreparingResponse $event): void
    {
        try {
            if ($this->laraowl->executionStageIs(ExecutionStage::Action)) {
                $this->laraowl->stage(ExecutionStage::Render);
            }
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
