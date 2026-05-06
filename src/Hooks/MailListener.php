<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Mail\Events\MessageSending;
use Illuminate\Mail\Events\MessageSent;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class MailListener
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(MessageSending|MessageSent $event): void
    {
        try {
            $this->laraowl->mail($event);
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
