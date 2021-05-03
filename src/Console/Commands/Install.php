<?php

namespace Northwestern\SysDev\DirectoryLookupComponent\Console\Commands;

use Illuminate\Console\GeneratorCommand;

/**
 * @codeCoverageIgnore
 */
class Install extends GeneratorCommand
{
    protected $signature = 'dynamic-forms:directory:install';
    protected $description = 'Installs Northwestern Directory Search component for Dynamic Forms';
    protected $type = 'Controller';

    public function handle()
    {
        $this->comment('Publishing lookup controller...');
        parent::handle();
        $this->newLine();

        $this->comment('Publishing JS assets...');
        $this->callSilent('vendor:publish', ['--tag' => 'dynamic-forms-directory-search-js']);
        $this->newLine();

        $this->info('The Northwestern Directory Search component for Dynamic Forms has been installed.');
        $this->newLine();

        $this->info('There are two additional steps you must complete:');
        $this->newLine();

        $this->info("\t1. Add a route to your dynamic-forms group:");
        $this->newLine();
        $this->info("\t\tRoute::get('directory/{search}', Controllers\DynamicFormsDirectoryController::class)->name('directory');");
        $this->newLine();

        $this->info("\t2. Register the JavaScript component in your resources/js/formio/index.js, where it says to load custom code:");
        $this->newLine();
        $this->info("\t\timport NuDirectoryLookup from \"../directory-search\";");
        $this->info("\t\timport NuDirectoryEditForm from \"../directory-search/form\";");
        $this->newLine();
        $this->info("\t\t// Find the 'If you want to load custom code' block and below it:");
        $this->info("\t\tFormio.use(NuDirectoryLookup);");
        $this->info("\t\tFormio.Components.components.nuDirectoryLookup.editForm = NuDirectoryEditForm;");
    }

    protected function getNameInput()
    {
        return 'DynamicFormsDirectoryController';
    }

    protected function getStub()
    {
        return __DIR__ . '/../../../stubs/DynamicFormsDirectoryController.stub';
    }

    protected function getDefaultNamespace($rootNamespace)
    {
        return $rootNamespace . '\Http\Controllers';
    }
}
