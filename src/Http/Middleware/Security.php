<?php

namespace Sicaboy\LaravelSecurity\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Sicaboy\LaravelSecurity\Model\UserExtendSecurity;

class Security
{

    protected $generator;
    protected $user;

    public function __construct(UrlGenerator $generator)
    {
        $this->generator = $generator;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // If user not login
        $closure = config('laravel-security.auth_user_closure', function() {
            return Auth::user();
        });
        $this->user = call_user_func($closure);
        if (!$this->user) {
            return redirect()->route(config('laravel-mfa.login_route', 'login'));
        }

        if ($redirect = $this->handleCheckLockedAccount()) {
            return $redirect;
        }

        if ($redirect = $this->handleForceChangePassword()) {
            return $redirect;
        }

        return $next($request);
    }

    protected function handleCheckLockedAccount()
    {
        // Already checked in this session
        if (Session::has('check_locked_account_completed')) {
            return false;
        }
//        Session::put('check_locked_account_completed', true);
        $modelClassName = config('laravel-security.database.user_security_model');
        $accountLocked = $modelClassName::where('user_id', $this->user->id)
            ->where('status', '<=', UserExtendSecurity::STATUS_LOCKED)
            ->exists();

        if ($accountLocked) {
            $func = config('laravel-security.password_policy.auto_lockout_inactive_accounts.locked_account_closure');
            return call_user_func($func);
        }
        return false;
    }

    protected function handleForceChangePassword()
    {
        // Function not enabled
        if (config('laravel-security.password_policy.force_change_password.enabled') !== true) {
            return false;
        }

        // Already checked in this session
        if (Session::has('force_change_password_completed')) {
            return false;
        }

        $days = config('laravel-security.password_policy.force_change_password.days_after_last_change', 90);
        $modelClassName = config('laravel-security.database.user_security_model');
        $passwordRecentlyUpdated = $modelClassName::where('user_id', $this->user->id)
            ->whereDate('last_password_updated_at', '>', Carbon::now()->subDays($days))
            ->exists();

        // No need to change password
        if ($passwordRecentlyUpdated) {
            Session::put('force_change_password_completed', true);
            return false;
        }

        // Need to change password
        $url = config('laravel-security.password_policy.force_change_password.change_password_url');
        $url .= (strpos($url, '?') === false) ? '?' : '&';
        $url .= 'referer=' . urlencode($this->generator->previous());
        return redirect()->to($url);
    }
}
