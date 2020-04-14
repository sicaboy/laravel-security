# Laravel Security Components

[![Latest Stable Version](https://poser.pugx.org/sicaboy/laravel-security/v/stable.svg)](https://packagist.org/packages/sicaboy/laravel-security)
[![License](https://poser.pugx.org/sicaboy/laravel-security/license.svg)](LICENSE.md)
[![Total Downloads](https://img.shields.io/packagist/dt/sicaboy/laravel-security.svg?style=flat-square)](https://packagist.org/packages/sicaboy/laravel-security)

## Introduction

This package can be used to enhance the user security of Laravel projects.

## Installation

Requirements:
- [PHP](https://php.net) 5.5+ 
- [Composer](https://getcomposer.org)

To get the latest version of Laravel Security, simply run:

```
composer require sicaboy/laravel-security
```

Then do vendor publish:

```
php artisan vendor:publish --provider="Sicaboy\LaravelSecurity\LaravelSecurityServiceProvider"
```

After publishing, you can modify templates and config in:

```
app/config/laravel-security.php
resources/views/vendor/laravel-security/
resources/lang/en/laravel-security.php
```

If you're on Laravel < 5.5, you'll need to register the service provider. Open up `config/app.php` and add the following to the `providers` array:

```php
Siaboy\LaravelSecurity\LaravelSecurityServiceProvider::class,
```


# Features

## Disallow user to use a common password or a used password

**Verify the user-provided password is not one of the top 10,000 worst passwords** as analyzed by a respectable IT security analyst. Read about all 
[ here](https://xato.net/10-000-top-passwords-6d6380716fe0#.473dkcjfm),
[here(wired)](http://www.wired.com/2013/12/web-semantics-the-ten-thousand-worst-passwords/) or
[here(telegram)](http://www.telegraph.co.uk/technology/internet-security/10303159/Most-common-and-hackable-passwords-on-the-internet.html)


#### Available validators rules

- [NotCommonPassword](src/Rules/NotCommonPassword.php) - Avoid user to use a common used password

- [NotAUsedPassword](src/Rules/NotAUsedPassword.php) - Avoid user to use a password which has been used before

```php
// Add rule instance to the field validation rules list
public function rules()
{
    return [
        'password_field' => [
            'required',
            'confirmed',
            'min:8',
            'regex:/[a-z]/',      // must contain at least one lowercase letter
            'regex:/[A-Z]/',      // must contain at least one uppercase letter
            'regex:/[0-9]/',      // must contain at least one digit
            //...
            new \Sicaboy\LaravelSecurity\Rules\NotCommonPassword(),
            new \Sicaboy\LaravelSecurity\Rules\NotAUsedPassword($userId),
            // Also you need to call event, examples in the next section
        ],
    ];
}
```

#### CAUTION: Extra event you need to call 

User login and register events have been automatically traced.
While there is an extra event you should add to call explicitly. 

```php
// Call on user password change
event(new \Illuminate\Auth\Events\PasswordReset($user));

// If you are using custom login, register and reset password actions which are not the Laravel built-in ones, you will need to call event in your function accordingly.
event(new \Illuminate\Auth\Events\Login($user)); 
event(new \Illuminate\Auth\Events\Registered($user));
event(new \Illuminate\Auth\Events\PasswordReset($user)); 
```

## Password Policies

#### Available policies

- Delete accounts with days of no activity
- Lockout accounts with days of no activity
- Force change password every x days

1. To enable the first two policies, you need to set `enabled` to `true` in `config/laravel-security.php` as below:

```php
'password_policy' => [
    // Delete accounts with days of no activity
    'auto_delete_inactive_accounts' => [
        'enabled' => true,
        ...
    ],

    // Lock out accounts with days of no activity
    'auto_lockout_inactive_accounts' => [
        'enabled' => true,
        ...
    ],
]
```

2. To reject locked accounts and force user to change their password every x days, you will need to use this middleware

```php
Route::middleware(['security'])->group(function () {
    ...
});
```

and set `enabled` to `true` and `change_password_url` in `config/laravel-security.php` as below:

```php
'password_policy' => [
    // Force change password every x days
    'force_change_password' => [
        'enabled' => true,
        'days_after_last_change' => 90, // every 90 days
        'change_password_url' => '/user/change-password', // Change My Password page URL
    ],
]
```

3. Add the following commands to `app/Console/Kernel.php` of your application. **Implement to one instance if using web server clusters**

```php
protected function schedule(Schedule $schedule)
{
    $schedule->command(\Sicaboy\LaravelSecurity\Console\Commands\DeleteInactiveAccounts::class)
             ->hourly();
    $schedule->command(\Sicaboy\LaravelSecurity\Console\Commands\LockoutInactiveAccounts::class)
             ->hourly();
    ...
}
```
3. Make sure you add the [Laravel scheduler](https://laravel.com/docs/7.x/scheduling#introduction) in your crontab  **Implement to one instance if using web server clusters**

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```  

## Multi-factor Authentication

This feature has been moved to [sicaboy/laravel-mfa](https://github.com/sicaboy/laravel-mfa)

## TODO

- Ability to split `extended_security` table to multiple tables. or other methods to support websites with huge user mount.

- Add cron job to remove too old password records to avoid heavy table. 

## Changelog

Please see [CHANGELOG](CHANGELOG.md) for more information on what has changed recently.

## Contributing

Please feel free to fork this package and contribute by submitting a pull request to enhance the functionalities.

## License

The MIT License (MIT). Please see [License File](LICENSE.md) for more information.
