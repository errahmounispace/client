<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Queue\Events\JobProcessing;
use Laraowl\Client\Compatibility;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class PolyfillContextHydration
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(JobProcessing $event): void
    {
        try {
            $laraowl = $event->job->payload()['laraowl'] ?? [];

            Compatibility::$context = [
                'laraowl_trace_id' => $laraowl['laraowl_trace_id'] ?? null,
                'laraowl_should_sample' => $laraowl['laraowl_should_sample'] ?? null,
                'laraowl_user_id' => $laraowl['laraowl_user_id'] ?? '',
            ];
        } catch (Throwable $e) {
            $this->laraowl->report($e);

            Compatibility::$context = [];
        }
    }
}
