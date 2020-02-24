<?php namespace WebEd\Base\Pages\Providers;

use Illuminate\Support\ServiceProvider;
use Schema;
use Illuminate\Database\Schema\Blueprint;

class InstallModuleServiceProvider extends ServiceProvider
{
    protected $module = 'webed-pages';

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {

    }

    public function boot()
    {
        /**
         * Determine when our app booted
         */
        app()->booted(function () {
            /**
             * Register permissions
             */
            acl_permission()
                ->registerPermission('View pages', 'view-pages', $this->module)
                ->registerPermission('Create pages', 'create-pages', $this->module)
                ->registerPermission('Edit pages', 'edit-pages', $this->module)
                ->registerPermission('Delete pages', 'delete-pages', $this->module);
        });
    }
}
