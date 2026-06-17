<?php

namespace Laraowl\Client;

use Illuminate\Auth\AuthManager;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;
use Laraowl\Client\Types\Str;
use Throwable;

use function call_user_func;

/**
 * @internal
 */
final class UserProvider
{
    private ?object $rememberedUser = null;

    /**
     * @var (callable(): (null|(callable(Authenticatable): array{id: mixed, name?: mixed, username?: mixed})))
     */
    public $userDetailsResolverResolver;

    /**
     * @var array{id: mixed, name?: mixed, username?: mixed}
     */
    private ?array $resolvedDetails;

    /**
     * @var (callable(callable(AuthManager): mixed): mixed)
     */
    private $withAuth;

    /**
     * @var (callable(): (callable(Throwable, bool): void))
     */
    private $reportResolver;

    private bool $alreadyReportedResolvingUserIdException = false;

    public function __construct(
        callable $withAuth,
        callable $userDetailsResolverResolver,
        callable $reportResolver,
    ) {
        $this->withAuth = $withAuth;
        $this->userDetailsResolverResolver = $userDetailsResolverResolver;
        $this->reportResolver = $reportResolver;
    }

    /**
     * @return string|LazyValue<string>
     */
    public function id(): LazyValue|string
    {
        return $this->withAuth(function ($auth) {
            if (! $auth->hasResolvedGuards()) {
                return $this->lazyUserId();
            }

            if ($auth->hasUser()) {
                return $this->userId($auth->user()); // @phpstan-ignore argument.type
            }

            if ($this->rememberedUser) {
                return $this->userId($this->rememberedUser);
            }

            return $this->lazyUserId();
        });
    }

    /**
     * @return LazyValue<string>
     */
    private function lazyUserId(): LazyValue
    {
        return new LazyValue(function () {
            return $this->resolvedUserId();
        });
    }

    public function resolvedUserId(): string
    {
        return $this->withAuth(function ($auth) {
            if (! $auth->hasResolvedGuards()) {
                return Compatibility::getUserIdFromContext() ?: $this->getGuestId();
            }

            if ($auth->hasUser()) {
                return $this->userId($auth->user()); // @phpstan-ignore argument.type
            }

            if ($this->rememberedUser) {
                return $this->userId($this->rememberedUser);
            }

            return Compatibility::getUserIdFromContext() ?: $this->getGuestId();
        });
    }

    private function getGuestId(): string
    {
        try {
            $request = function_exists('request') ? request() : null;

            if ($request instanceof \Illuminate\Http\Request) {
                return 'guest_'.substr(md5($request->ip().$request->userAgent()), 0, 12);
            }
        } catch (Throwable) {
            //
        }

        return '';
    }

    private function userId(object $user): string
    {
        try {
            return Str::tinyText((string) ($this->resolvedDetails($user)['id'] ?? '')); // @phpstan-ignore cast.string
        } catch (Throwable $e) {
            $this->reportResolvingUserIdException($e);

            return '';
        }
    }

    /**
     * @return array{ id: mixed, name?: mixed, username?: mixed }|null
     */
    public function details(): ?array
    {
        $user = $this->withAuth(fn ($auth) => $auth->hasResolvedGuards()
            ? $auth->user() ?? $this->rememberedUser
            : $this->rememberedUser);

        return $this->resolvedDetails($user);
    }

    /**
     * @return array{ id: mixed, name?: mixed, username?: mixed }|null
     */
    private function resolvedDetails(?object $user): ?array
    {
        if ($user === null) {
            return null;
        }

        if (isset($this->resolvedDetails)) {
            return $this->resolvedDetails;
        }

        try {
            $id = $this->identifierFor($user);
        } catch (Throwable $e) {
            $this->reportResolvingUserIdException($e);

            return null;
        }

        $resolver = call_user_func($this->userDetailsResolverResolver);

        if (! is_null($resolver) && $user instanceof Authenticatable) {
            return $this->resolvedDetails = [
                'id' => $id,
                ...$resolver($user),
            ];
        }

        return $this->resolvedDetails = [
            'id' => $id,
            'name' => $user->name ?? '',
            'username' => $user->email ?? '',
        ];
    }

    private function identifierFor(object $user): mixed
    {
        if ($user instanceof Authenticatable) {
            return $user->getAuthIdentifier();
        }

        if ($user instanceof Model) {
            return $user->getKey();
        }

        return $user->id ?? null;
    }

    public function remember(object $user): void
    {
        $this->rememberedUser = $user;
    }

    public function flush(): void
    {
        $this->rememberedUser = null;
        $this->resolvedDetails = null;
        $this->alreadyReportedResolvingUserIdException = false;
    }

    private function reportResolvingUserIdException(Throwable $e): void
    {
        if ($this->alreadyReportedResolvingUserIdException) {
            return;
        }

        $this->alreadyReportedResolvingUserIdException = true;

        $report = call_user_func($this->reportResolver);

        $report($e, true);
    }

    /**
     * @template TValue
     *
     * @param  callable(AuthManager): TValue  $callback
     * @return TValue
     */
    private function withAuth(callable $callback): mixed
    {
        return ($this->withAuth)($callback);
    }
}
