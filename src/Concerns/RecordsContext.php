<?php

namespace Laraowl\Client\Concerns;

use Illuminate\Support\Facades\Context;
use Laraowl\Client\Compatibility;
use Laraowl\Client\Facades\LaraowlClient;
use Laraowl\Client\Types\Str;
use Throwable;

use function json_encode;

/**
 * @internal
 */
trait RecordsContext
{
    private function serializedContext(): string
    {
        if (! Compatibility::$contextExists) {
            return '';
        }

        try {
            return Str::text(json_encode((object) Context::all(), JSON_THROW_ON_ERROR | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE | JSON_PRESERVE_ZERO_FRACTION));
        } catch (Throwable $e) {
            LaraowlClient::unrecoverableExceptionOccurred($e);

            return '{"_laraowl_error":"Failed to serialize context"}';
        }
    }
}
