<?php

namespace Centaur\Tests;

use Session;
use Sentinel;
use Illuminate\Foundation\AliasLoader;
use Illuminate\Foundation\Exceptions\Handler;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Contracts\Debug\ExceptionHandler;
use Illuminate\View\Middleware\ShareErrorsFromSession;
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
    public function setUp()
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

        // Add
        $app->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware(StartSession::class);
        $app->make('Illuminate\Contracts\Http\Kernel')->pushMiddleware(ShareErrorsFromSession::class);

        // Include routes needed for testing
        include __DIR__ . '/../src/routes.php';
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
     * Overide the default application exception handler
     *
     * Thanks Adam!
     * https://gist.github.com/adamwathan/125847c7e3f16b88fa33a9f8b42333da
     */
    protected function disableExceptionHandling()
    {
        $this->oldExceptionHandler = $this->app->make(ExceptionHandler::class);
        $this->app->instance(ExceptionHandler::class, new class extends Handler {
            public function __construct() {}
            public function report(\Exception $e) {}
            public function render($request, \Exception $e) {
                throw $e;
            }
        });
    }

    /**
     * A helper method for creating an active user session
     * @param  string $email
     * @return $this
     */
    public function signIn($email)
    {
        Session::start();

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

    /**
     * A session must be started before the csrf token will be available
     * @return string
     */
    public function getCsrfToken()
    {
        Session::start();
        return csrf_token();
    }
}
