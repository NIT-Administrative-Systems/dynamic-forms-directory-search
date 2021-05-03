<?php

namespace Northwestern\SysDev\DirectoryLookupComponent;

use Illuminate\Support\ServiceProvider;
use Northwestern\SysDev\DirectoryLookupComponent\Console\Commands\Install;
use Northwestern\SysDev\DynamicForms\ComponentRegistry;

class DirectoryLookupComponentProvider extends ServiceProvider
{
    public function boot()
    {
        $this->registerCommands();
        $this->registerPublishing();

        /** @var ComponentRegistry $registry */
        $registry = $this->app->make(ComponentRegistry::class);

        $registry->register(DirectoryLookup::class);
    }

    /**
     * @codeCoverageIgnore
     */
    protected function registerCommands(): void
    {
        if ($this->app->runningInConsole()) {
            $this->commands([
                Install::class,
            ]);
        }
    }

    /**
     * @codeCoverageIgnore
     */
    private function registerPublishing(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../dist' => resource_path('js/directory-search'),
            ], 'dynamic-forms-directory-search-js');
        }
    }
}
