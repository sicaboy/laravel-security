<?php

namespace Sicaboy\LaravelSecurity\Helpers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Session;

class SecurityHelper
{
    public function getConfigByGroup($key, $group, $default = null) {
        if ($group && $value = config("laravel-security.group.{$group}.{$key}")) {
            return $value;
        }
        if ($value = config("laravel-security.default.{$key}")) {
            return $value;
        }
        return $default;
    }

    public function getAuthUserModel($configGroup) {
        $closure = $this->getConfigByGroup('auth_user_closure', $configGroup, function() {
            return Auth::user();
        });
        return call_user_func($closure);
    }
}
