<?php

namespace Laraowl\Client\Records;

use Laraowl\Client\QueryConnectionType;

final class Query
{
    public function __construct(
        public string $sql,
        public readonly string $file,
        public readonly int $line,
        public readonly int $duration,
        public readonly string $connection,
        public readonly QueryConnectionType $connectionType,
    ) {
        //
    }
}
