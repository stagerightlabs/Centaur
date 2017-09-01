# Centaur

[![Travis](https://img.shields.io/travis/SRLabs/Centaur.svg)](https://travis-ci.org/SRLabs/Centaur)
[![Packagist](https://img.shields.io/packagist/dt/SRLabs/Centaur.svg)](https://packagist.org/packages/srlabs/centaur)
[![Packagist](https://img.shields.io/packagist/v/SRLabs/Centaur.svg)](https://packagist.org/packages/srlabs/centaur)
[![Packagist](https://img.shields.io/packagist/l/SRLabs/Centaur.svg)](https://packagist.org/packages/srlabs/centaur)

This package provides an opinionated implementation of  [Cartalyst Sentinel](https://cartalyst.com/manual/sentinel/2.0) for [Laravel](https://github.com/laravel/laravel).

Make sure you use the version most appropriate for the type of Laravel application you have:

| Laravel Version  | Centaur Version  | Packagist Branch |
|---|---|---|
| 5.1.*  | 1.*  | ```"srlabs/centaur": "1.*"``` |
| 5.2.*  | 2.*  | ```"srlabs/centaur": "2.*"``` |
| 5.3.*  | 3.*  | ```"srlabs/centaur": "3.*"``` |
| 5.4.*  | 4.*  | ```"srlabs/centaur": "4.*"``` |
| 5.5.*  | 5.*  | ```"srlabs/centaur": "5.*"``` |

## Installation
**Install the Package Via Composer:**

```shell
$ composer require srlabs/centaur
```

**Add the Service Provider to your ```config/app.php``` file:**

```php
'providers' => array(
    ...
    Centaur\CentaurServiceProvider::class,
    ...
)
```

This package will not make use of [automatic package discovery](https://laravel.com/docs/5.5/packages#package-discovery) - you will need to register it manually. This is intentional.

## Usage in New Applications
If you are starting a new Laravel 5.* application, this package provides a convenient way to get up and running with ```Cartalyst\Sentinel``` very quickly.   Start by removing the default auth scaffolding that ships with a new Laravel 5.1 application:

```shell
$ php artisan centaur:spruce
```

Next, use Centaur's scaffolding command to create basic Auth Controllers and Views in your application:

```shell
$ php artisan centaur:scaffold
```

Publish the ```Cartalyst\Sentinel``` assets:

```shell
$ php artisan vendor:publish --provider="Cartalyst\Sentinel\Laravel\SentinelServiceProvider"
```

Run your database migrations:
```shell
$ php artisan migrate
```

Run the Database Seeder. You may need to re-generate the autoloader before this will work:
```shell
$ composer dump-autoload
$ php artisan db:seed --class="SentinelDatabaseSeeder"
```

You will also need to add these routes to your ```routes.php``` file:
```php
// Authorization
Route::get('/login', ['as' => 'auth.login.form', 'uses' => 'Auth\SessionController@getLogin']);
Route::post('/login', ['as' => 'auth.login.attempt', 'uses' => 'Auth\SessionController@postLogin']);
Route::get('/logout', ['as' => 'auth.logout', 'uses' => 'Auth\SessionController@getLogout']);

// Registration
Route::get('register', ['as' => 'auth.register.form', 'uses' => 'Auth\RegistrationController@getRegister']);
Route::post('register', ['as' => 'auth.register.attempt', 'uses' => 'Auth\RegistrationController@postRegister']);

// Activation
Route::get('activate/{code}', ['as' => 'auth.activation.attempt', 'uses' => 'Auth\RegistrationController@getActivate']);
Route::get('resend', ['as' => 'auth.activation.request', 'uses' => 'Auth\RegistrationController@getResend']);
Route::post('resend', ['as' => 'auth.activation.resend', 'uses' => 'Auth\RegistrationController@postResend']);

// Password Reset
Route::get('password/reset/{code}', ['as' => 'auth.password.reset.form', 'uses' => 'Auth\PasswordController@getReset']);
Route::post('password/reset/{code}', ['as' => 'auth.password.reset.attempt', 'uses' => 'Auth\PasswordController@postReset']);
Route::get('password/reset', ['as' => 'auth.password.request.form', 'uses' => 'Auth\PasswordController@getRequest']);
Route::post('password/reset', ['as' => 'auth.password.request.attempt', 'uses' => 'Auth\PasswordController@postRequest']);

// Users
Route::resource('users', 'UserController');

// Roles
Route::resource('roles', 'RoleController');

// Dashboard
Route::get('dashboard', ['as' => 'dashboard', 'uses' => function() {
    return view('centaur.dashboard');
}]);
```

This is only meant to be a starting point; you can change them as you see fit.  Make sure you read through your new Auth Controllers and understand how they work before you make any changes.

Centaur automatically installs Sentinel and registers the ```Sentinel```, ```Activations```, and ```Reminders``` aliases for you.  Detailed instructions for using Sentinel [can be found here](https://cartalyst.com/manual/sentinel/2.0).

## Usage in Existing Applications
If you already have already built out your auth views and controllers, the best way to make use of this package is to inject the ```AuthManager``` into your controllers and use it as a wrapper for Sentinel.   Detailed information about the ```AuthManager``` methods [can be found here](https://github.com/SRLabs/Centaur/wiki/AuthManager-Methods-and-Responses).

## Using Customized Middleware
It is possible that the behavior of the Middleware that comes with this package might not suit your exact needs.  To adjust the middleware, create a copy of the problematic Centaur Middleware class in your ```app/Http/Middleware``` directory - this new class can be given any name you would like.   You can then adjust the middleware references in your controllers and/or routes file to use the new class, or you can bind the new class to the Centaur Middleware class name in your App service provider, as such:

```php
// app/providers/AppServiceProvider.php
/**
 * Register any application services.
 *
 * @return void
 */
public function register()
{
    $this->app->bind('Centaur\Middleware\SentinelGuest', function ($app) {
        return new \App\Http\Middleware\AlternativeGuestMiddleware;
    });
}
```
