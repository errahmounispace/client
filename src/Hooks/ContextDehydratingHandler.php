<?php

namespace Laraowl\Client\Hooks;

use Illuminate\Log\Context\Repository;
use Laraowl\Client\Core;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Throwable;

/**
 * @internal
 */
final class ContextDehydratingHandler
{
    /**
     * @param  Core<RequestState|CommandState>  $laraowl
     */
    public function __construct(
        private Core $laraowl,
    ) {
        //
    }

    public function __invoke(Repository $context): void
    {
        try {
            if (($context->getHidden('laraowl_user_id') ?? '') === '') {
                $context->addHidden('laraowl_user_id', $this->laraowl->executionState->user->resolvedUserId());
            }
        } catch (Throwable $e) {
            $this->laraowl->report($e);
        }
    }
}
