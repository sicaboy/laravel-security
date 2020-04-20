<?php

namespace Sicaboy\LaravelSecurity\Listeners;

use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

class InsertUsedPassword
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * @param  object  $event
     * @return void
     */
    public function handle($event)
    {
        $modelClassName = config('laravel-security.database.password_history_model');
        return $modelClassName::create([
            'user_id' => $event->user->id,
            'user_class' => get_class($event->user),
            'password' => $event->user->password
        ]);
    }
}
