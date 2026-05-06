<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\Foundation\Exceptions\Handler;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class ExceptionHandlerResolvedHandler
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(ExceptionHandler $handler): void
    {
        try {
            if ($handler instanceof Handler) {
                /**
                 * @see \Laraowl\Client\Records\Exception
                 */
                $handler->reportable(new ReportableHandler($this->laraowl));
            }
        } catch (Throwable $e) {
            $this->laraowl->report($e, handled: true);
        }
    }
}
