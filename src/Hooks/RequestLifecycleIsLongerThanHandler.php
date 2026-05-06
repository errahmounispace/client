<?php

namespace Laraowl\Client\Hooks;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\RequestState;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @internal
 */
final class RequestLifecycleIsLongerThanHandler
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Carbon $startedAt, Request $request, Response $response): void
    {
        try {
            $this->laraowl->stage(ExecutionStage::End);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        try {
            $this->laraowl->captureUser();
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        try {
            $this->laraowl->request($request, $response);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        $this->laraowl->finishExecution();
    }
}
