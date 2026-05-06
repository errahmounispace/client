<?php

namespace Laraowl\Client\Hooks;

use GuzzleHttp\Promise\PromiseInterface;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Throwable;

/**
 * @internal
 */
final class GuzzleMiddleware
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    /**
     * TODO record the failed responses as well.
     */
    public function __invoke(callable $handler): callable
    {
        if ($this->laraowl->config['filtering']['ignore_outgoing_requests'] || $this->laraowl->paused()) {
            return $handler;
        }

        return function (RequestInterface $request, array $options) use ($handler): PromiseInterface {
            try {
                $startMicrotime = $this->laraowl->clock->microtime();
            } catch (Throwable $e) {
                $this->laraowl->report($e, handled: true);

                return $handler($request, $options);
            }

            return $handler($request, $options)->then(function (ResponseInterface $response) use ($request, $startMicrotime): ResponseInterface {
                try {
                    $endMicrotime = $this->laraowl->clock->microtime();

                    $this->laraowl->outgoingRequest(
                        $startMicrotime, $endMicrotime,
                        $request, $response,
                    );
                } catch (Throwable $e) {
                    $this->laraowl->report($e, handled: true);
                }

                return $response;
            });
        };
    }
}
