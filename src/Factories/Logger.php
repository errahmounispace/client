<?php

namespace Laraowl\Client\Factories;

use DateTimeZone;
use Laraowl\Client\Core;
use Laraowl\Client\Hooks\LogHandler;
use Laraowl\Client\Hooks\LogRecordProcessor;
use Laraowl\Client\State\CommandState;
use Laraowl\Client\State\RequestState;
use Monolog\Logger as Monolog;
use Monolog\Processor\PsrLogMessageProcessor;
use Psr\Log\LoggerInterface;

/**
 * @internal
 */
final class Logger
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
     * @param  array<string, mixed>&array{level: \Psr\Log\LogLevel::*}  $config
     */
    public function __invoke(array $config): LoggerInterface
    {
        return new Monolog(
            name: 'laraowl',
            handlers: [
                new LogHandler(
                    laraowl: $this->laraowl,
                    level: Monolog::toMonologLevel($config['level']),
                    // There is some unexpected behaviour in the framework when
                    // using a log stack that causes monolog processors to leak
                    // and apply their side-effects to other log handlers in
                    // the stack. Instead of passing processors to the monolog
                    // instance, as you would usually expect, we pass them to
                    // our handler to apply manually. This allows us to keep
                    // the side-effects of the processors isolated to
                    // LaraowlClient's handler when used in a stack of handlers.
                    processors: [
                        new LogRecordProcessor($this->laraowl, 'Y-m-d H:i:s.uP'),
                        new PsrLogMessageProcessor('Y-m-d H:i:s.uP'),
                    ],
                ),
            ],
            timezone: new DateTimeZone('UTC'),
        );
    }
}
