<?php

namespace BoilingSoup\Sneeze\Console;

use Illuminate\Filesystem\Filesystem;

trait InstallsApiStack
{
    /**
     * Install the API Breeze stack.
     *
     * @return int|null
     */
    protected function installApiStack()
    {
        $this->runCommands(['php artisan install:api']);

        $files = new Filesystem;

        // Controllers...
        $files->ensureDirectoryExists(app_path('Http/Controllers/Auth'));
        $files->copyDirectory(__DIR__ . '/../../stubs/api/app/Http/Controllers/Auth', app_path('Http/Controllers/Auth'));

        // Middleware...
        $files->copyDirectory(__DIR__ . '/../../stubs/api/app/Http/Middleware', app_path('Http/Middleware'));

        $this->installMiddlewareAliases([
            'verified' => '\App\Http\Middleware\EnsureEmailIsVerified::class',
        ]);

        // $this->installMiddleware([
        //     '\Laravel\Sanctum\Http\Middleware\EnsureFrontendRequestsAreStateful::class',
        // ], 'api', 'prepend');

        // Requests...
        $files->ensureDirectoryExists(app_path('Http/Requests/Auth'));
        $files->copyDirectory(__DIR__ . '/../../stubs/api/app/Http/Requests/Auth', app_path('Http/Requests/Auth'));

        // Providers...
        $files->copyDirectory(__DIR__ . '/../../stubs/api/app/Providers', app_path('Providers'));

        // Routes...
        copy(__DIR__ . '/../../stubs/api/routes/api.php', base_path('routes/api.php'));
        copy(__DIR__ . '/../../stubs/api/routes/web.php', base_path('routes/web.php'));
        copy(__DIR__ . '/../../stubs/api/routes/auth.php', base_path('routes/auth.php'));

        // Configuration...
        $files->copyDirectory(__DIR__ . '/../../stubs/api/config', config_path());

        // Environment...
        if (! $files->exists(base_path('.env'))) {
            copy(base_path('.env.example'), base_path('.env'));
        }

        file_put_contents(
            base_path('.env'),
            preg_replace('/APP_URL=(.*)/', 'APP_URL=http://localhost:8000' . PHP_EOL . 'FRONTEND_URL=http://localhost:3000', file_get_contents(base_path('.env')))
        );

        // Tests...
        if (! $this->installTests()) {
            return 1;
        }

        $files->delete(base_path('tests/Feature/Auth/PasswordConfirmationTest.php'));

        $this->components->info('Sneeze scaffolding installed successfully.');
    }
}
