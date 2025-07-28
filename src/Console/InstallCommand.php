<?php

namespace BoilingSoup\Sneeze\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Str;
use RuntimeException;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Process\Process;

#[AsCommand(name: 'sneeze:install')]
class InstallCommand extends Command
{
    use InstallsApiStack;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sneeze:install
                            {--pest : Indicate that Pest should be installed}
                            {--composer=global : Absolute path to the Composer binary which should be used to install packages}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Install the Sneeze controllers and resources';

    /**
     * Execute the console command.
     *
     * @return int|null
     */
    public function handle()
    {
        return $this->installApiStack();
    }

    /**
     * Install Sneeze's tests.
     *
     * @return bool
     */
    protected function installTests()
    {
        (new Filesystem)->ensureDirectoryExists(base_path('tests/Feature'));

        $stubStack = 'api';

        // $stubStack = match ($this->argument('stack')) {
        //     'api' => 'api',
        //     'livewire' => 'livewire-common',
        //     'livewire-functional' => 'livewire-common',
        //     default => 'default',
        // };

        if ($this->option('pest') || $this->isUsingPest()) {
            if ($this->hasComposerPackage('phpunit/phpunit')) {
                $this->removeComposerPackages(['phpunit/phpunit'], true);
            }

            if (! $this->requireComposerPackages(['pestphp/pest', 'pestphp/pest-plugin-laravel'], true)) {
                return false;
            }

            (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/' . $stubStack . '/pest-tests/Feature', base_path('tests/Feature'));
            (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/' . $stubStack . '/pest-tests/Unit', base_path('tests/Unit'));
            (new Filesystem)->copy(__DIR__ . '/../../stubs/' . $stubStack . '/pest-tests/Pest.php', base_path('tests/Pest.php'));
        } else {
            (new Filesystem)->copyDirectory(__DIR__ . '/../../stubs/' . $stubStack . '/tests/Feature', base_path('tests/Feature'));
        }

        return true;
    }

    /**
     * Install the given middleware names into the application.
     *
     * @param  array|string  $name
     * @param  string  $group
     * @param  string  $modifier
     * @return void
     */
    // protected function installMiddleware($names, $group = 'web', $modifier = 'append')
    // {
    //     $bootstrapApp = file_get_contents(base_path('bootstrap/app.php'));
    //
    //     $names = collect(Arr::wrap($names))
    //         ->filter(fn($name) => ! Str::contains($bootstrapApp, $name))
    //         ->whenNotEmpty(function ($names) use ($bootstrapApp, $group, $modifier) {
    //             $names = $names->map(fn($name) => "$name")->implode(',' . PHP_EOL . '            ');
    //
    //             $bootstrapApp = str_replace(
    //                 '->withMiddleware(function (Middleware $middleware): void {',
    //                 '->withMiddleware(function (Middleware $middleware): void {'
    //                     . PHP_EOL . "        \$middleware->$group($modifier: ["
    //                     . PHP_EOL . "            $names,"
    //                     . PHP_EOL . '        ]);'
    //                     . PHP_EOL,
    //                 $bootstrapApp,
    //             );
    //
    //             file_put_contents(base_path('bootstrap/app.php'), $bootstrapApp);
    //         });
    // }

    /**
     * Install the given middleware aliases into the application.
     *
     * @param  array  $aliases
     * @return void
     */
    protected function installMiddlewareAliases($aliases)
    {
        $bootstrapApp = file_get_contents(base_path('bootstrap/app.php'));

        $aliases = collect($aliases)
            ->filter(fn($alias) => ! Str::contains($bootstrapApp, $alias))
            ->whenNotEmpty(function ($aliases) use ($bootstrapApp) {
                $aliases = $aliases->map(fn($name, $alias) => "'$alias' => $name")->implode(',' . PHP_EOL . '            ');

                if (str_contains(
                    $bootstrapApp,
                    '->withMiddleware(function (Middleware $middleware): void {'
                )) {
                    $bootstrapApp = str_replace(
                        '->withMiddleware(function (Middleware $middleware): void {',
                        '->withMiddleware(function (Middleware $middleware): void {'
                            . PHP_EOL . '        $middleware->alias(['
                            . PHP_EOL . "            $aliases,"
                            . PHP_EOL . '        ]);'
                            . PHP_EOL,
                        $bootstrapApp,
                    );
                } else if (str_contains( // NOTE: Laravel 11 did not have :void return type
                    $bootstrapApp,
                    '->withMiddleware(function (Middleware $middleware) {'
                )) {
                    $bootstrapApp = str_replace(
                        '->withMiddleware(function (Middleware $middleware) {',
                        '->withMiddleware(function (Middleware $middleware) {'
                            . PHP_EOL . '        $middleware->alias(['
                            . PHP_EOL . "            $aliases,"
                            . PHP_EOL . '        ]);'
                            . PHP_EOL,
                        $bootstrapApp,
                    );
                }

                file_put_contents(base_path('bootstrap/app.php'), $bootstrapApp);
            });
    }

    /**
     * Determine if the given Composer package is installed.
     *
     * @param  string  $package
     * @return bool
     */
    protected function hasComposerPackage($package)
    {
        $packages = json_decode(file_get_contents(base_path('composer.json')), true);

        return array_key_exists($package, $packages['require'] ?? [])
            || array_key_exists($package, $packages['require-dev'] ?? []);
    }

    /**
     * Installs the given Composer Packages into the application.
     *
     * @param  bool  $asDev
     * @return bool
     */
    protected function requireComposerPackages(array $packages, $asDev = false)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'require'];
        }

        $command = array_merge(
            $command ?? ['composer', 'require'],
            $packages,
            $asDev ? ['--dev'] : [],
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            }) === 0;
    }

    /**
     * Removes the given Composer Packages from the application.
     *
     * @param  bool  $asDev
     * @return bool
     */
    protected function removeComposerPackages(array $packages, $asDev = false)
    {
        $composer = $this->option('composer');

        if ($composer !== 'global') {
            $command = ['php', $composer, 'remove'];
        }

        $command = array_merge(
            $command ?? ['composer', 'remove'],
            $packages,
            $asDev ? ['--dev'] : [],
        );

        return (new Process($command, base_path(), ['COMPOSER_MEMORY_LIMIT' => '-1']))
            ->setTimeout(null)
            ->run(function ($type, $output) {
                $this->output->write($output);
            }) === 0;
    }

    /**
     * Run the given commands.
     *
     * @param  array  $commands
     * @return void
     */
    protected function runCommands($commands)
    {
        $process = Process::fromShellCommandline(implode(' && ', $commands), null, null, null, null);

        if ('\\' !== DIRECTORY_SEPARATOR && file_exists('/dev/tty') && is_readable('/dev/tty')) {
            try {
                $process->setTty(true);
            } catch (RuntimeException $e) {
                $this->output->writeln('  <bg=yellow;fg=black> WARN </> ' . $e->getMessage() . PHP_EOL);
            }
        }

        $process->run(function ($type, $line) {
            $this->output->write('    ' . $line);
        });
    }

    /**
     * Determine whether the project is already using Pest.
     *
     * @return bool
     */
    protected function isUsingPest()
    {
        /**
         * @disregard P1009 Undefined type
         */
        return class_exists(\Pest\TestSuite::class);
    }
}
