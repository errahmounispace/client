<?php

namespace Laraowl\Client\Hooks;

use Laraowl\Client\Compatibility;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class PolyfillContextDehydration
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
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function __invoke(mixed $connection, mixed $queue, array $payload): array
    {
        $context = Compatibility::$context;

        try {
            if (($context['laraowl_user_id'] ?? '') === '') {
                $context['laraowl_user_id'] = $this->laraowl->executionState->user->resolvedUserId();
            }

            return [
                ...$payload,
                'laraowl' => [
                    ...($payload['laraowl'] ?? []), // @phpstan-ignore arrayUnpacking.nonIterable
                    'laraowl_trace_id' => $context['laraowl_trace_id'] ?? null,
                    'laraowl_should_sample' => $context['laraowl_should_sample'] ?? null,
                    'laraowl_user_id' => $context['laraowl_user_id'],
                ],
            ];
        } catch (Throwable $e) {
            $this->laraowl->report($e);

            return $payload;
        }
    }
}
