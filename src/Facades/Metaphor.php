<?php

namespace Sukohi\Metaphor\Facades;

use Illuminate\Support\Facades\Facade;

class Metaphor extends Facade {

    /**
    * Get the registered name of the component.
    *
    * @return string
    */
    protected static function getFacadeAccessor() { return 'metaphor'; }

}