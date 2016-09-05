<?php

namespace Centaur\Console;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class CentaurSpruce extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'centaur:spruce';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Remove the default auth scaffolding from a new Laravel 5.1 application';

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
        $files = [
            '/database/migrations/2014_10_12_000000_create_users_table.php',
            '/database/migrations/2014_10_12_100000_create_password_resets_table.php',
            '/app/Http/Controllers/Auth/ForgotPasswordController.php',
            '/app/Http/Controllers/Auth/LoginController.php',
            '/app/Http/Controllers/Auth/RegisterController.php',
            '/app/Http/Controllers/Auth/ResetPasswordController.php',
        ];

        foreach ($files as $file) {
            if (file_exists(base_path($file))) {
                $this->files->delete(base_path($file));
                $this->info('Removed File: ' . $file);
            }
        }
    }
}
