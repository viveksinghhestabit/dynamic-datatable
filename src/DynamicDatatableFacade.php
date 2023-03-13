<?php

namespace Viveksingh;

use Illuminate\Support\Facades\Facade;

/**
 * @see \Viveksingh\DynamicDatatable\Skeleton\SkeletonClass
 */
class DynamicDatatableFacade extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return 'dynamic-datatable';
    }
}
