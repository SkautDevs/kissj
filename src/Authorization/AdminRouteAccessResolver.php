<?php

declare(strict_types=1);

namespace kissj\Authorization;

use kissj\User\User;
use Psr\Container\ContainerInterface;
use ReflectionAttribute;
use ReflectionException;
use ReflectionMethod;
use Slim\App;
use Slim\Interfaces\RouteInterface;

readonly class AdminRouteAccessResolver
{
    /**
     * @param App<ContainerInterface> $app
     */
    public function __construct(
        private App $app,
    ) {
    }

    public function canAccessRoute(User $user, string $routeName): bool
    {
        return $this->allowsRoute($user, $this->app->getRouteCollector()->getNamedRoute($routeName));
    }

    public function allowsRoute(User $user, RouteInterface $route): bool
    {
        if ($user->isTopAdmin()) {
            return true;
        }

        $callable = $route->getCallable();
        if (is_string($callable) === false) {
            return false;
        }

        try {
            $method = ReflectionMethod::createFromMethodName($callable);
        } catch (ReflectionException) {
            return false;
        }

        return array_filter(
            $method->getAttributes(AdminRouteAccess::class, ReflectionAttribute::IS_INSTANCEOF),
            fn (ReflectionAttribute $attribute): bool => $attribute->newInstance()->allows($user->role),
        ) !== [];
    }
}
