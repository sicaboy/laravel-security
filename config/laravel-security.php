<?php

return [

    'default' => [
        'login_route' => 'login',
        'user_model' => \App\User::class,
        'auth_user_closure' => function() {
            return \Illuminate\Support\Facades\Auth::user();
        },
        'password_policy' => [
            // Delete accounts with days of no activity
            'auto_delete_inactive_accounts' => [
                'enabled' => false,
                'days_after_last_login' => 365,
                'email_notification' => [
                    'enabled' => true,
                    'template' => 'laravel-security::emails.account-terminated',
                    'subject' => 'Your account has been terminated',
                ],
            ],
            // Lock out accounts with days of no activity
            'auto_lockout_inactive_accounts' => [
                'enabled' => false,
                'days_after_last_login' => 90,
                'locked_account_closure' => function($request) {
                    \Illuminate\Support\Facades\Auth::logout();
                    return redirect()->route('security.account-locked');
                    /*
                    Example of using AJAX
                    return response()->json([
                        'error' => 'Account locked',
                        'code' => 'ACCOUNT_LOCKED',
                        'url' => route('security.account-locked')
                    ], 403);*/
                },
                'email_notification' => [
                    'enabled' => true,
                    'template' => 'laravel-security::emails.account-locked',
                    'subject' => 'Your account has been locked due to no activity',
                ],
            ],
            // Force change password every x days
            'force_change_password' => [
                'enabled' => false,
                'days_after_last_change' => 90,
                'change_password_url' => '/change-password',
            ],
        ],
    ],

    'group' => [ // Example of override default configs
        'admin' => [ // Middleware: 'security:admin'
            'login_route' => 'admin.login',
            'user_model' => \Encore\Admin\Auth\Database\Administrator::class,
            'auth_user_closure' => function() {
                return \Encore\Admin\Facades\Admin::user();
            },
            'password_policy' => [
                'auto_lockout_inactive_accounts' => [
                    'locked_account_closure' => function($request) {
                        \Encore\Admin\Facades\Admin::guard()->logout();
                        return  redirect()->route('security.account-locked');
                    },
                ],
                'force_change_password' => [
                    'change_password_url' => config('admin.route.prefix') . '/change-password',
                ],
            ],
        ]
    ],

    'database' => [
        'connection' => '', // Database connection for running database migration.
        'user_security_table' => 'user_extend_security',
        'user_security_model' => Sicaboy\LaravelSecurity\Model\UserExtendSecurity::class,
        'password_history_table' => 'password_history',
        'password_history_model' => Sicaboy\LaravelSecurity\Model\PasswordHistory::class,
    ],
];
