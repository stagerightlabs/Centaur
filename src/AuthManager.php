<?php

namespace Centaur;

use DB;
use Lang;
use Exception;
use Carbon\Carbon;
use Centaur\Dispatches\Reply;
use InvalidArgumentException;
use Centaur\Replies\FailureReply;
use Centaur\Replies\SuccessReply;
use Centaur\Replies\ExceptionReply;
use Cartalyst\Sentinel\Users\UserInterface;

class AuthManager
{
    /**
     * @var Cartalyst\Sentinel\SentinelSentinel
     */
    protected $sentinel;

    public function __construct()
    {
        // Retrieve the Sentinel Singleton(s) from the IoC
        $this->sentinel = app()->make('sentinel');
        $this->activations = app()->make('sentinel.activations');
        $this->reminders = app()->make('sentinel.reminders');
    }

    /**
     * Attempt to authenticate a user
     * @param  array $credentials
     * @param  boolean $remember
     * @param  boolean $login
     * @return Reply
     */
    public function authenticate($credentials, $remember = false, $login = true)
    {
        try {
            $user = $this->sentinel->authenticate($credentials, $remember, $login);
        } catch (Exception $e) {
            return $this->returnException($e);
        }

        if ($user) {
            $message = request()->ajax() ?
                $this->translate('session_initated', 'You have been authenticated.') : null;
            return new SuccessReply($message);
        }

        $message = $this->translate('failed_authorization', 'Access denied due to invalid credentials.');
        return new FailureReply($message);
    }

    /**
     * Terminate a user's session.  If no user is provided, the currently active
     * user will be signed out.
     * @param  UserInterface|null $user
     * @param  boolean            $everywhere
     * @return Reply
     */
    public function logout(UserInterface $user = null, $everywhere = false)
    {
        try {
            $user = $this->sentinel->logout($user, $everywhere);
        } catch (Exception $e) {
            return $this->returnException($e);
        }

        if (!$this->sentinel->check()) {
            $message = $this->translate('user_logout', 'You have been logged out');
            return new SuccessReply($message);
        }

        $message = $this->translate('generic_problem', 'There was a problem. Please contact a site administrator.');
        return new FailureReply($message);
    }

    /**
     * Register a new user
     * @param  array $credentials
     * @param  boolean $activation
     * @return Reply
     */
    public function register(array $credentials, $activation = false)
    {
        try {
            $exists = $this->sentinel->findUserByCredentials($credentials);

            if ($exists) {
                throw new InvalidArgumentException("Invalid credentials provided");
            }

            $user = $this->sentinel->register($credentials, $activation);
            if (!$activation) {
                $activation = $this->activations->create($user);
            }
        } catch (Exception $e) {
            return $this->returnException($e);
        }

        if ($user) {
            $message = $this->translate('registration_success', 'Registration complete');
            return new SuccessReply($message, ['user' => $user, 'activation' => $activation]);
        }

        $message = $this->translate('registration_failed', 'Registration denied due to invalid credentials.');
        return new FailureReply($message);
    }

    /**
     * Activate a user based on the provided activation code
     * @param  string $code
     * @return Reply
     */
    public function activate($code)
    {
        try {
            // Attempt to fetch the user via the activation code
            $activation = $this->activations
                           ->createModel()
                           ->newQuery()
                           ->where('code', $code)
                           ->where('completed', false)
                           ->where('created_at', '>', Carbon::now()->subSeconds(259200))
                           ->first();

            if (!$activation) {
                $message = $this->translate("activation_problem", "Invalid or expired activation code.");
                throw new InvalidArgumentException($message);
            }
            $user = $this->sentinel->findUserById($activation->user_id);

            // Complete the user's activation
            $this->activations->complete($user, $code);

            // While we are here, lets remove any expired activations
            $this->activations->removeExpired();
        } catch (Exception $e) {
            return $this->returnException($e);
        }

        if ($user) {
            $message = $this->translate("activation_success", "Activation successful.");
            return new SuccessReply($message);
        }

        $message = $this->translate('activation_failed', 'There was a problem activating your account.');
        return new FailureReply($message);
    }

    /**
     * Attempt a password reset
     * @param  string $code
     * @param  string $password
     * @return Reply
     */
    public function resetPassword($code, $password)
    {
        try {
            // Attempt to fetch the user via the activation code
            $reminder = $this->reminders
                           ->createModel()
                           ->newQuery()
                           ->where('code', $code)
                           ->where('completed', false)
                           ->where('created_at', '>', Carbon::now()->subSeconds(259200))
                           ->first();

            if (!$reminder) {
                $message = $this->translate("password_reset_problem", "Invalid or expired password reset code; please request a new link.");
                throw new InvalidArgumentException($message);
            }
            $user = $this->sentinel->findUserById($reminder->user_id);

            // Complete the user's password reminder
            $this->reminders->complete($user, $code, $password);

            // While we are here, lets remove any expired reminders
            $this->reminders->removeExpired();
        } catch (Exception $e) {
            return $this->returnException($e);
        }

        if ($user) {
            $message = $this->translate("password_reset_success", "Password reset successful.");
            return new SuccessReply($message);
        }

        $message = $this->translate('password_reset_failed', 'There was a problem reseting your password.');
        return new FailureReply($message);
    }

    /**
     * Return any caught exceptions in a ExceptionDispatch DTO
     * @param  Exception $e
     * @return ExcpetionDispatch
     */
    protected function returnException(Exception $e)
    {
        $key = snake_case(class_basename($e));
        $message = $this->translate($key, $e->getMessage());

        return new ExceptionReply($message, [], $e);
    }

    /**
     * Helper method for facilitating string translation
     * @param  string $key
     * @param  string $message
     * @return string
     */
    protected function translate($key, $message)
    {
        $key = 'centaur.' . $key;

        if (Lang::has($key)) {
            $message = trans($key);
        }

        return $message;
    }
}
