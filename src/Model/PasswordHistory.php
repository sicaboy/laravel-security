<?php

namespace Sicaboy\LaravelSecurity\Model;

use Illuminate\Database\Eloquent\Model;

class PasswordHistory extends Model
{

    protected $guarded = ['id'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param  array  $attributes
     */
    public function __construct(array $attributes = [])
    {
        $connection = config('laravel-security.database.connection') ?: config('database.default');
        $this->setConnection($connection);
        $this->setTable(config('laravel-security.database.password_history_table'));
        parent::__construct($attributes);
    }

    public function user()
    {
        if (!$this->user_class) {
            return null;
        }
        return $this->belongsTo($this->user_class ?: 'App\User');
    }

}
