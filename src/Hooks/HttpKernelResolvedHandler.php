<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Http\Kernel as KernelContract;
use Illuminate\Foundation\Events\Terminating;
use Illuminate\Foundation\Http\Kernel;
use Laraowl\Client\Core;
use Laraowl\Client\Facades\LaraowlClient;
use Laraowl\Client\Http\Middleware\Sample;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class HttpKernelResolvedHandler
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(KernelContract $kernel, Application $app): void
    {
        if (! $kernel instanceof Kernel) {
            return;
        }

        try {
            /**
             * @see \Laraowl\Client\ExecutionStage::End
             * @see \Laraowl\Client\Records\Request
             * @see \Laraowl\Client\Core::finishExecution()
             */
            $kernel->whenRequestLifecycleIsLongerThan(-1, new RequestLifecycleIsLongerThanHandler($this->laraowl));
        } catch (Throwable $e) {
            LaraowlClient::unrecoverableExceptionOccurred($e);
        }

        try {
            /**
             * @see \Laraowl\Client\ExecutionStage::Terminating
             */
            $kernel->prependMiddleware(GlobalMiddleware::class);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }

        try {
            $kernel->prependToMiddlewarePriority(Sample::class);
        } catch (Throwable $e) {
            $this->laraowl->report($e);
        }
    }
}
