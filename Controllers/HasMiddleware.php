<?php

namespace QuantaForge\Routing\Controllers;

interface HasMiddleware
{
    /**
     * Get the middleware that should be assigned to the controller.
     *
     * @return \QuantaForge\Routing\Controllers\Middleware|array
     */
    public static function middleware();
}
