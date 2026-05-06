<?php

namespace Laraowl\Client\Hooks;

use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Laravel\Octane\Events\RequestReceived;
use Throwable;

/**
 * @internal
 */
final class OctaneListener
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(private Core $laraowl)
    {
        //
    }

    public function __invoke(RequestReceived $event): void // @phpstan-ignore class.notFound
    {
        try {
            $this->laraowl->prepareForNextRequest();
        } catch (Throwable $e) {
            $this->laraowl->report($e);
        }
    }
}
