<?php

namespace Centaur\Console;

use ReflectionClass;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Console\DetectsApplicationNamespace;


class CentaurScaffold extends Command
{
    use DetectsApplicationNamespace;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'centaur:scaffold {--remove}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate an auth scaffold for a new application';

    /**
     * The user's application namespace
     *
     * @var string
     */
    protected $namespace;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Filesystem $files)
    {
        parent::__construct();
        $this->files = $files;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Are we being asked to remove the scaffolding?
        $removal = $this->option('remove');

        // Determine the package file path
        $centaurFilename = with(new ReflectionClass('Centaur\CentaurServiceProvider'))->getFileName();
        $centaurPath = dirname($centaurFilename);

        // Get the current application namespace
        $this->namespace = str_replace('\\', '', $this->getAppNamespace());

        // Check our destination directories
        if (!$removal) {
            if (!is_dir(app_path('Http/Controllers/Auth')) && $removal == false) {
                $this->files->makeDirectory(app_path('Http/Controllers/Auth'));
            }

            if (!is_dir(base_path('resources/views/centaur'))) {
                $this->files->makeDirectory(base_path('resources/views/centaur'));
            }

            if (!is_dir(base_path('resources/views/centaur/auth'))) {
                $this->files->makeDirectory(base_path('resources/views/centaur/auth'));
            }

            if (!is_dir(base_path('resources/views/centaur/email'))) {
                $this->files->makeDirectory(base_path('resources/views/centaur/email'));
            }

            if (!is_dir(base_path('resources/views/centaur/roles'))) {
                $this->files->makeDirectory(base_path('resources/views/centaur/roles'));
            }

            if (!is_dir(base_path('resources/views/centaur/users'))) {
                $this->files->makeDirectory(base_path('resources/views/centaur/users'));
            }
        }

        // Namespaced Files
        $namespaced = [
            '/Controllers/Auth/PasswordController.php' => base_path('app/Http/Controllers/Auth/PasswordController.php'),
            '/Controllers/Auth/RegistrationController.php' => base_path('app/Http/Controllers/Auth/RegistrationController.php'),
            '/Controllers/Auth/SessionController.php' => base_path('app/Http/Controllers/Auth/SessionController.php'),
            '/Controllers/RoleController.php' => base_path('app/Http/Controllers/RoleController.php'),
            '/Controllers/UserController.php' => base_path('app/Http/Controllers/UserController.php'),
        ];

        // Copy Namespaced files
        foreach ($namespaced as $file  => $destination) {
            if ($this->files->exists($destination)) {
                if ($removal) {
                    $this->files->delete($destination);
                    $this->info('Removed File: ' . $destination);
                } else {
                    $this->info('File Already Exists: ' . $destination);
                }
            } else {
                if (!$removal) {
                    $this->files->copy($centaurPath . $file, $destination);
                    $this->updateNamespace($destination);
                    $this->info($destination);
                }
            }
        }

        // Non-namespaced files
        $assets = [
            '/../views/auth/login.blade.php' => base_path('resources/views/centaur/auth/login.blade.php'),
            '/../views/auth/password.blade.php' => base_path('resources/views/centaur/auth/password.blade.php'),
            '/../views/auth/register.blade.php' => base_path('resources/views/centaur/auth/register.blade.php'),
            '/../views/auth/resend.blade.php' => base_path('resources/views/centaur/auth/resend.blade.php'),
            '/../views/auth/reset.blade.php' => base_path('resources/views/centaur/auth/reset.blade.php'),
            '/../views/email/reset.blade.php' => base_path('resources/views/centaur/email/reset.blade.php'),
            '/../views/email/welcome.blade.php' => base_path('resources/views/centaur/email/welcome.blade.php'),
            '/../views/roles/create.blade.php' => base_path('resources/views/centaur/roles/create.blade.php'),
            '/../views/roles/edit.blade.php' => base_path('resources/views/centaur/roles/edit.blade.php'),
            '/../views/roles/index.blade.php' => base_path('resources/views/centaur/roles/index.blade.php'),
            '/../views/users/create.blade.php' => base_path('resources/views/centaur/users/create.blade.php'),
            '/../views/users/edit.blade.php' => base_path('resources/views/centaur/users/edit.blade.php'),
            '/../views/users/index.blade.php' => base_path('resources/views/centaur/users/index.blade.php'),
            '/../views/dashboard.blade.php' => base_path('resources/views/centaur/dashboard.blade.php'),
            '/../views/layout.blade.php' => base_path('resources/views/centaur/layout.blade.php'),
            '/../views/notifications.blade.php' => base_path('resources/views/centaur/notifications.blade.php'),
            '/../public/restfulizer.js' => base_path('public/restfulizer.js'),
            '/../seeds/SentinelDatabaseSeeder.php' => base_path('database/seeds/SentinelDatabaseSeeder.php'),
        ];

        // Copy Non-namespaced files
        foreach ($assets as $file  => $destination) {
            if ($this->files->exists($destination)) {
                if ($removal) {
                    $this->files->delete($destination);
                    $this->info('Removed File: ' . $destination);
                } else {
                    $this->info('File Already Exists: ' . $destination);
                }
            } else {
                if (!$removal) {
                    $this->files->copy($centaurPath . $file, $destination);
                    $this->info($destination);
                }
            }
        }
    }

    /**
     * Replace the given string in the given file.
     *
     * @param  string  $path
     * @param  string|array  $search
     * @param  string|array  $replace
     * @return void
     */
    protected function updateNamespace($path)
    {
        $search = [
            'namespace Centaur;',
            'Centaur\\Controllers',
        ];
        $replace = [
            'namespace '.$this->namespace.';',
            $this->namespace.'\\Http\\Controllers',
        ];

        $this->files->put($path, str_replace($search, $replace, $this->files->get($path)));
    }
}
