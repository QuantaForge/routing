<?php

namespace QuantaForge\Routing\Matching;

use QuantaForge\Http\Request;
use QuantaForge\Routing\Route;

class HostValidator implements ValidatorInterface
{
    /**
     * Validate a given rule against a route and request.
     *
     * @param  \QuantaForge\Routing\Route  $route
     * @param  \QuantaForge\Http\Request  $request
     * @return bool
     */
    public function matches(Route $route, Request $request)
    {
        $hostRegex = $route->getCompiled()->getHostRegex();

        if (is_null($hostRegex)) {
            return true;
        }

        return preg_match($hostRegex, $request->getHost());
    }
}
