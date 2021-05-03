# Northwestern Directory Search Component for Dynamic Forms [![PHPUnit Tests](https://github.com/NIT-Administrative-Systems/dynamic-forms-directory-search/actions/workflows/phpunit.yml/badge.svg)](https://github.com/NIT-Administrative-Systems/dynamic-forms-directory-search/actions/workflows/phpunit.yml) [![Coverage Status](https://coveralls.io/repos/github/NIT-Administrative-Systems/dynamic-forms-directory-search/badge.svg?branch=develop)](https://coveralls.io/github/NIT-Administrative-Systems/dynamic-forms-directory-search?branch=develop) 
This is a [Dynamic Forms for Laravel](https://github.com/NIT-Administrative-Systems/dynamic-forms) component that does a lookup in the Northwestern directory.

It provides both the form builder/presentation UI and a controller for doing the lookups.

## Installation
You should already be using Dynamic Forms for Laravel. These instructions assume you have already set it up.

You will need an app with access to Directory Search - Basic. You can request access in the API Service Registry. Your API key should exist in the `.env`:

```
DIRECTORY_SEARCH_API_KEY=

# This defaults to prod when it is not specified.
DIRECTORY_SEARCH_URL=https://northwestern-prod.apigee.net/directory-search
```

Once ready, you can install the component via Composer:

```
composer require northwestern-sysdev/dynamic-forms-directory-search
php artisan dynamic-forms:directory:install
```

You will need to make two further changes:

1. Add a route for the new controller, inside the existing Dynamic Forms route group:

    ```php
    Route::get('directory/{search}', Controllers\DynamicFormsDirectoryController::class)->name('directory');
    ```

2. Register the UI component with the Formiojs library in your `resources/js/formio/index.js` file:

    ```js
    import NuDirectoryLookup from "../directory-search";
    import NuDirectoryEditForm from "../directory-search/form";

    // . . . skip some lines, look for this comment & add the code below it:
    
    // -------------------------------------------------------------------------
    // If you want to load custom code (like additional components), do it here!
    // -------------------------------------------------------------------------
    Formio.use(NuDirectoryLookup);
    Formio.Components.components.nuDirectoryLookup.editForm = NuDirectoryEditForm;
    ```

    Then run Laravel Mix to rebuild with the new component.

The component will show up in the form builder in the advanced section. You can rearrange it in the menu like any other component. 

## Contributing
Pull requests from members of the Northwestern community are welcome!
