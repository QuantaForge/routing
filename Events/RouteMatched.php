<?php

namespace QuantaForge\Routing\Events;

class RouteMatched
{
    /**
     * The route instance.
     *
     * @var \QuantaForge\Routing\Route
     */
    public $route;

    /**
     * The request instance.
     *
     * @var \QuantaForge\Http\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  \QuantaForge\Routing\Route  $route
     * @param  \QuantaForge\Http\Request  $request
     * @return void
     */
    public function __construct($route, $request)
    {
        $this->route = $route;
        $this->request = $request;
    }
}
