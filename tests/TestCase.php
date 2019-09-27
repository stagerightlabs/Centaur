<?php

namespace Centaur\Tests;

use Illuminate\Support\Facades\Route;
use Cartalyst\Sentinel\Laravel\Facades\Sentinel;
use Orchestra\Testbench\TestCase as OrchestraTestCase;

class TestCase extends OrchestraTestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * The active user object, if there is one
     * @var null
     */
    protected $user = null;

    /**
     * Setup the test environment.
     *
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();

        $this->prepareTestingDatabase();
    }

    /**
     * Define environment setup.
     *
     * @param  \Illuminate\Foundation\Application  $app
     * @return void
     */
    protected function getEnvironmentSetUp($app)
    {
        // Setup default database to use sqlite :memory:
        $app['config']->set('database.default', 'testbench');
        $app['config']->set('database.connections.testbench', [
            'driver'   => 'sqlite',
            'database' => __DIR__ . '/data/database.sqlite',
            'prefix'   => '',
        ]);

        // We don't want to accidentally send any mail
        $app['config']->set('mail.pretend', true);
        $app['config']->set('mail.from', ['from' => 'noreply@example.com', 'name' => null]);

        // Include routes needed for testing
        // include __DIR__ . '/../src/routes.php';

        Route::middleware('web')
            ->namespace('App\Http\Controllers')
            ->group(realpath(__DIR__ . '/../src/routes.php'));
    }

    public function enableExceptionHandler()
    {
        $handler = $this->app->make('Orchestra\Testbench\Exceptions\ApplicationHandler');
        $this->app->instance('Illuminate\Contracts\Debug\ExceptionHandler', $handler);
    }

    /**
     * Load the package service provider
     * @param  \Illuminate\Foundation\Application $app
     * @return Array
     */
    protected function getPackageProviders($app)
    {
        return ['Centaur\CentaurServiceProvider'];
    }

    /**
     * Prepare the sqlite database
     * http://www.chrisduell.com/blog/development/speeding-up-unit-tests-in-php/
     *
     * @return void
     */
    public function prepareTestingDatabase()
    {
        exec('cp ' . __DIR__ . '/data/staging.sqlite ' . __DIR__ . '/data/database.sqlite');
    }

    /**
     * A helper method for creating an active user session
     * @param  string $email
     * @return $this
     */
    public function signIn($email)
    {
        $this->user = Sentinel::findUserByCredentials(['email' => $email]);

        if ($this->user) {
            Sentinel::login($this->user);
        }

        return $this;
    }

    /**
     * A helper method for ending an active user session
     * @return $this
     */
    public function signOut()
    {
        Sentinel::logout(null, true);
        $this->user = null;

        return $this;
    }
}
