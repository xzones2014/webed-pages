<?php

use \WebEd\Base\Pages\Repositories\Contracts\PageRepositoryContract;

if (!function_exists('get_pages')) {
    /**
     * @param mixed
     */
    function get_pages(array $params = [])
    {
        return app(PageRepositoryContract::class)->getPages($params);
    }
}