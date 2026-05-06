<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Foundation\Bootstrap\HandleExceptions;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

use function str_repeat;

/**
 * @internal
 */
final class ReportableHandler
{
    public ?string $reservedMemory;

    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        $this->reservedMemory = str_repeat('n', 32768);
    }

    public function __invoke(Throwable $e): void
    {
        if (HandleExceptions::$reservedMemory === null) {
            $this->reservedMemory = null;
        }

        if ($this->laraowl->executionState->source === 'schedule') {
            return;
        }

        $this->laraowl->report($e);
    }
}
