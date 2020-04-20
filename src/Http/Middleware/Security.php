<?php

namespace Sicaboy\LaravelSecurity\Http\Middleware;

use Carbon\Carbon;
use Closure;
use Illuminate\Routing\UrlGenerator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Sicaboy\LaravelSecurity\Helpers\SecurityHelper;
use Sicaboy\LaravelSecurity\Model\UserExtendSecurity;

class Security
{

    protected $generator;
    protected $user;
    protected $helper;
    protected $request;

    public function __construct(UrlGenerator $generator, SecurityHelper $helper)
    {
        $this->generator = $generator;
        $this->helper = $helper;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next, $group = 'default')
    {
        $this->user = $this->helper->getAuthUserModel($group);
        $this->request = $request;
        if (!$this->user) {
            // No Auth::user returned. Not login yet
            return $request->wantsJson()
                ? response()->json([
                    'error' => 'Login required',
                    'url' => $this->helper->getConfigByGroup('login_route', $group, 'login')
                ], 403)
                : redirect()->route(
                    $this->helper->getConfigByGroup('login_route', $group, 'login')
                );
        }

        if ($redirect = $this->handleCheckLockedAccount($group)) {
            return $redirect;
        }

        if ($redirect = $this->handleForceChangePassword($group)) {
            return $redirect;
        }

        return $next($request);
    }

    protected function handleCheckLockedAccount($group)
    {
        // Already checked in this session
        if (Session::has('check_locked_account_completed')) {
            return false;
        }
//        Session::put('check_locked_account_completed', true);
        $modelClassName = config('laravel-security.database.user_security_model');
        $accountLocked = $modelClassName::where('user_id', $this->user->id)
            ->where('user_class', get_class($this->user))
            ->where('status', '<=', UserExtendSecurity::STATUS_LOCKED)
            ->exists();

        if ($accountLocked) {
            $func = $this->helper->getConfigByGroup('password_policy.auto_lockout_inactive_accounts.locked_account_closure', $group);
            return call_user_func($func, $this->request);
        }
        return false;
    }

    protected function handleForceChangePassword($group)
    {
        // Function not enabled
        if ($this->helper->getConfigByGroup('password_policy.force_change_password.enabled', $group) !== true) {
            return false;
        }

        // Already checked in this session
        if (Session::has('force_change_password_completed')) {
            return false;
        }

        $days = $this->helper->getConfigByGroup('password_policy.force_change_password.days_after_last_change', $group, 90);
        $modelClassName = config('laravel-security.database.user_security_model');
        $passwordRecentlyUpdated = $modelClassName::where('user_id', $this->user->id)
            ->where('user_class', get_class($this->user))
            ->whereDate('last_password_updated_at', '>', Carbon::now()->subDays($days))
            ->exists();

        // No need to change password
        if ($passwordRecentlyUpdated) {
            Session::put('force_change_password_completed', true);
            return false;
        }

        // Need to change password
        $url = $this->helper->getConfigByGroup('password_policy.force_change_password.change_password_url', $group);
        $url .= (strpos($url, '?') === false) ? '?' : '&';
        $url .= 'referer=' . urlencode($this->generator->previous());


        return $this->request->wantsJson()
            ? response()->json([
                'error' => 'Change password required',
                'message' => 'Please update your password',
                'code' => 'PASSWORD_EXPIRED',
                'url' => $url
            ], 403)
            : redirect()->to($url);
    }
}
