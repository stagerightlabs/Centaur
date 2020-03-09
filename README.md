# Centaur

[![Travis](https://img.shields.io/travis/stagerightlabs/Centaur.svg)](https://travis-ci.org/stagerightlabs/Centaur)
[![Packagist](https://img.shields.io/packagist/dt/SRLabs/Centaur.svg)](https://packagist.org/packages/srlabs/centaur)
[![Packagist](https://img.shields.io/packagist/v/SRLabs/Centaur.svg)](https://packagist.org/packages/srlabs/centaur)
[![Packagist](https://img.shields.io/packagist/l/SRLabs/Centaur.svg)](https://packagist.org/packages/srlabs/centaur)

This package provides an opinionated implementation of  [Cartalyst Sentinel](https://cartalyst.com/manual/sentinel/2.0) for [Laravel](https://github.com/laravel/laravel).

Make sure you use the version most appropriate for the type of Laravel application you have:

| Laravel Version  | Centaur Version  | Packagist Branch |
|---|---|---|
| 5.8.*  | 8.*  | ```"srlabs/centaur": "8.*"``` |
| 6.0.*  | 9.*  | ```"srlabs/centaur": "9.*"``` |
| 7.0.*  | 10.*  | ```"srlabs/centaur": "10.*"``` |

If you are using an older version of Laravel, there are [other versions](https://packagist.org/packages/srlabs/centaur) available.

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
Route::get('login', 'Auth\SessionController@getLogin')->name('auth.login.form');
Route::post('login', 'Auth\SessionController@postLogin')->name('auth.login.attempt');
Route::any('logout', 'Auth\SessionController@getLogout')->name('auth.logout');

// Registration
Route::get('register', 'Auth\RegistrationController@getRegister')->name('auth.register.form');
Route::post('register', 'Auth\RegistrationController@postRegister')->name('auth.register.attempt');

// Activation
Route::get('activate/{code}', 'Auth\RegistrationController@getActivate')->name('auth.activation.attempt');
Route::get('resend', 'Auth\RegistrationController@getResend')->name('auth.activation.request');
Route::post('resend', 'Auth\RegistrationController@postResend')->name('auth.activation.resend');

// Password Reset
Route::get('password/reset/{code}', 'Auth\PasswordController@getReset')->name('auth.password.reset.form');
Route::post('password/reset/{code}', 'Auth\PasswordController@postReset')->name('auth.password.reset.attempt');
Route::get('password/reset', 'Auth\PasswordController@getRequest')->name('auth.password.request.form');
Route::post('password/reset', 'Auth\PasswordController@postRequest')->name('auth.password.request.attempt');

// Users
Route::resource('users', 'UserController');

// Roles
Route::resource('roles', 'RoleController');

// Dashboard
Route::get('dashboard', function () {
    return view('Centaur::dashboard');
})->name('dashboard');
```

This is only meant to be a starting point; you can change them as you see fit.  Make sure you read through your new Auth Controllers and understand how they work before you make any changes.

Centaur automatically installs Sentinel and registers the ```Sentinel```, ```Activations```, and ```Reminders``` aliases for you.  Detailed instructions for using Sentinel [can be found here](https://cartalyst.com/manual/sentinel/2.0).

If you do decide to make use of Laravel's `Route::resource()` option, you will need to use [Form Method Spoofing](https://github.com/SRLabs/Centaur/wiki/Form-Method-Spoofing) to access some of those generated routes.

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
