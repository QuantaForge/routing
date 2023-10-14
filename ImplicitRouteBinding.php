<?php

namespace QuantaForge\Routing;

use QuantaForge\Contracts\Routing\UrlRoutable;
use QuantaForge\Database\Eloquent\ModelNotFoundException;
use QuantaForge\Database\Eloquent\SoftDeletes;
use QuantaForge\Routing\Exceptions\BackedEnumCaseNotFoundException;
use QuantaForge\Support\Reflector;
use QuantaForge\Support\Str;

class ImplicitRouteBinding
{
    /**
     * Resolve the implicit route bindings for the given route.
     *
     * @param  \QuantaForge\Container\Container  $container
     * @param  \QuantaForge\Routing\Route  $route
     * @return void
     *
     * @throws \QuantaForge\Database\Eloquent\ModelNotFoundException<\QuantaForge\Database\Eloquent\Model>
     * @throws \QuantaForge\Routing\Exceptions\BackedEnumCaseNotFoundException
     */
    public static function resolveForRoute($container, $route)
    {
        $parameters = $route->parameters();

        $route = static::resolveBackedEnumsForRoute($route, $parameters);

        foreach ($route->signatureParameters(['subClass' => UrlRoutable::class]) as $parameter) {
            if (! $parameterName = static::getParameterName($parameter->getName(), $parameters)) {
                continue;
            }

            $parameterValue = $parameters[$parameterName];

            if ($parameterValue instanceof UrlRoutable) {
                continue;
            }

            $instance = $container->make(Reflector::getParameterClassName($parameter));

            $parent = $route->parentOfParameter($parameterName);

            $routeBindingMethod = $route->allowsTrashedBindings() && in_array(SoftDeletes::class, class_uses_recursive($instance))
                        ? 'resolveSoftDeletableRouteBinding'
                        : 'resolveRouteBinding';

            if ($parent instanceof UrlRoutable &&
                ! $route->preventsScopedBindings() &&
                ($route->enforcesScopedBindings() || array_key_exists($parameterName, $route->bindingFields()))) {
                $childRouteBindingMethod = $route->allowsTrashedBindings() && in_array(SoftDeletes::class, class_uses_recursive($instance))
                            ? 'resolveSoftDeletableChildRouteBinding'
                            : 'resolveChildRouteBinding';

                if (! $model = $parent->{$childRouteBindingMethod}(
                    $parameterName, $parameterValue, $route->bindingFieldFor($parameterName)
                )) {
                    throw (new ModelNotFoundException)->setModel(get_class($instance), [$parameterValue]);
                }
            } elseif (! $model = $instance->{$routeBindingMethod}($parameterValue, $route->bindingFieldFor($parameterName))) {
                throw (new ModelNotFoundException)->setModel(get_class($instance), [$parameterValue]);
            }

            $route->setParameter($parameterName, $model);
        }
    }

    /**
     * Resolve the Backed Enums route bindings for the route.
     *
     * @param  \QuantaForge\Routing\Route  $route
     * @param  array  $parameters
     * @return \QuantaForge\Routing\Route
     *
     * @throws \QuantaForge\Routing\Exceptions\BackedEnumCaseNotFoundException
     */
    protected static function resolveBackedEnumsForRoute($route, $parameters)
    {
        foreach ($route->signatureParameters(['backedEnum' => true]) as $parameter) {
            if (! $parameterName = static::getParameterName($parameter->getName(), $parameters)) {
                continue;
            }

            $parameterValue = $parameters[$parameterName];

            $backedEnumClass = $parameter->getType()?->getName();

            $backedEnum = $backedEnumClass::tryFrom((string) $parameterValue);

            if (is_null($backedEnum)) {
                throw new BackedEnumCaseNotFoundException($backedEnumClass, $parameterValue);
            }

            $route->setParameter($parameterName, $backedEnum);
        }

        return $route;
    }

    /**
     * Return the parameter name if it exists in the given parameters.
     *
     * @param  string  $name
     * @param  array  $parameters
     * @return string|null
     */
    protected static function getParameterName($name, $parameters)
    {
        if (array_key_exists($name, $parameters)) {
            return $name;
        }

        if (array_key_exists($snakedName = Str::snake($name), $parameters)) {
            return $snakedName;
        }
    }
}
