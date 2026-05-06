<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Http\Client\Factory;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class HttpClientFactoryResolvedHandler
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Factory $factory): void
    {
        try {
            /**
             * @see \Laraowl\Client\Records\OutgoingRequest
             */
            $factory->globalMiddleware($this->laraowl->guzzleMiddleware());
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
