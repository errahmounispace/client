<?php

namespace Laraowl\Client\Hooks;

use Closure;
use Illuminate\Http\Request;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class RouteMiddleware
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function handle(Request $request, Closure $next): mixed
    {
        try {
            $this->laraowl->stage(ExecutionStage::Action);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        $response = $next($request);

        // If an exception occurs in the action phase, the usual
        // ResponsePrepared event is not fired. This fallback
        // ensures that we go to the AfterMiddleware stage.
        try {
            $this->laraowl->stage(ExecutionStage::AfterMiddleware);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        return $response;
    }
}
