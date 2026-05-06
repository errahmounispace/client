<?php

namespace Laraowl\Client\Hooks;

use Closure;
use Illuminate\Http\Request;
use Laraowl\Client\Compatibility;
use Laraowl\Client\Core;
use Laraowl\Client\ExecutionStage;
use Laraowl\Client\Facades\LaraowlClient;
use Laraowl\Client\State\RequestState;
use Symfony\Component\HttpFoundation\Response;
use Throwable;

/**
 * @internal
 */
final class GlobalMiddleware
{
    private bool $hasHandledRequest = false;

    private bool $hasTerminated = false;

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
        if ($this->hasHandledRequest) {
            return $next($request);
        }

        $this->hasHandledRequest = true;

        try {
            $this->laraowl->configureRequestSampling();
        } catch (Throwable $e) {
            LaraowlClient::unrecoverableExceptionOccurred($e);
        }

        try {
            $this->laraowl->captureRequestPreview($request);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        if ($this->hasTerminated || Compatibility::$terminatingEventExists) {
            return;
        }

        $this->hasTerminated = true;

        try {
            $this->laraowl->stage(ExecutionStage::Terminating);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
