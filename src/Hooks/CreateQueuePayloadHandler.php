<?php

namespace Laraowl\Client\Hooks;

use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class CreateQueuePayloadHandler
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
        try {
            return [
                ...$payload,
                'laraowl' => [
                    ...($payload['laraowl'] ?? []),  // @phpstan-ignore arrayUnpacking.nonIterable
                    'job_id' => $this->laraowl->uuid->make(),
                ],
            ];
        } catch (Throwable $e) {
            $this->laraowl->report($e);

            return $payload;
        }
    }
}
