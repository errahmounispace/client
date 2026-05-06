<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Console\Events\ArtisanStarting;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Throwable;

/**
 * @internal
 */
final class ArtisanStartingListener
{
    /**
     * @param  Core<CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(ArtisanStarting $event): void
    {
        try {
            $this->laraowl->captureArtisan($event->artisan);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
