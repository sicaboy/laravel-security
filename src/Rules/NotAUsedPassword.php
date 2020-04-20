<?php


namespace Sicaboy\LaravelSecurity\Rules;

use Illuminate\Contracts\Validation\Rule;
use Hash;

class NotAUsedPassword implements Rule
{

    /** @var string */
    protected $modelClassName;

    /** @var string */
    protected $modelAttribute;

    /** @var string */
    protected $attribute;

    protected $user;

    public function __construct($user = null, $modelClassName = null)
    {
        if(!$modelClassName) {
            $modelClassName = config('laravel-security.database.password_history_model');
        }
        $this->user = $user;
        $this->modelClassName = $modelClassName;
    }


    public function passes($attribute, $value): bool
    {
        if (!$this->user) {
            return true;
        }
        // $this->attribute = $attribute;
        $model = $this->modelClassName::select('password');
        if(!empty($this->user->id)) {
            $model->where('user_id', $this->user->id);
            $model->where('user_class', get_class($this->user));
        }
        $allUsedPasswords = $model->get();
        $isOldPassword = false;
        foreach ($allUsedPasswords as $item) {
            if (Hash::check($value, $item->password)) {
                $isOldPassword = true;
            }
        }
        return !$isOldPassword;
    }

    public function message(): string
    {
        return __('laravel-security::message.not_a_used_password', [
            // 'attribute' => $this->attribute,
            // 'model' => $classBasename,
        ]);
    }
}
