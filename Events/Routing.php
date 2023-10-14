<?php

namespace QuantaForge\Routing\Events;

class Routing
{
    /**
     * The request instance.
     *
     * @var \QuantaForge\Http\Request
     */
    public $request;

    /**
     * Create a new event instance.
     *
     * @param  \QuantaForge\Http\Request  $request
     * @return void
     */
    public function __construct($request)
    {
        $this->request = $request;
    }
}
