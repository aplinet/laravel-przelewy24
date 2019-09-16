<?php

namespace Adams\Przelewy24\Facades;

use Illuminate\Support\Facades\Facade as BaseFacade;

final class Facade extends BaseFacade 
{
    /**
     * Return Laravel Framework facade accessor name.
     * 
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'przelewy24';
    }
}