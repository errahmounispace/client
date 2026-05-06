<?php

namespace Laraowl\Client\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Laraowl\Client\Core;
use Laraowl\Client\Facades\LaraowlClient;
use Laraowl\Client\State\RequestState;
use Throwable;

final class Sample
{
    /**
     * @param  Core<RequestState>  $laraowl
     */
    public function __construct(private Core $laraowl)
    {
        //
    }

    public static function rate(float $rate): string
    {
        $rate = (string) $rate;

        if ($rate === '0') {
            $rate = '0.0';
        }

        return self::class.':'.$rate;
    }

    public static function always(): string
    {
        return self::class.':1.0';
    }

    public static function never(): string
    {
        return self::class.':0.0';
    }

    public function handle(Request $request, Closure $next, float $rate): mixed
    {
        try {
            $this->laraowl->sample($rate);
        } catch (Throwable $e) {
            LaraowlClient::unrecoverableExceptionOccurred($e);
        }

        return $next($request);
    }
}
