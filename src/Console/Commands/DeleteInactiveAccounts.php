<?php

namespace Sicaboy\LaravelSecurity\Console\Commands;

use Carbon\Carbon;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Mail;
use Sicaboy\LaravelSecurity\Helpers\SecurityHelper;

class DeleteInactiveAccounts extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'laravel-security:delete-inactive-accounts';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Laravel Security Delete Inactive Accounts';

    protected $helper;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(SecurityHelper $helper)
    {
        parent::__construct();
        $this->helper = $helper;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle() {
        $groups = array_keys(config("laravel-security.group"));
        $groups[] = 'default';
        foreach ($groups as $group) {
            $this->handleByGroup($group);
        }
    }


    public function handleByGroup($group){
        // Delete accounts with xxx days of no activity
        if ($this->helper->getConfigByGroup('password_policy.auto_delete_inactive_accounts.enabled', $group) !== true) {
            $this->error("Disabled");
            return;
        }
        $days = $this->helper->getConfigByGroup('password_policy.auto_delete_inactive_accounts.days_after_last_login', $group);
        $this->info("Deleting accounts with {$days} days of no activity");
        $modelClassName = config('laravel-security.database.user_security_model');
        $userExtends = $modelClassName::whereDate('last_loggein_at', '<', Carbon::today()->subDays($days))
            ->where('user_class', $this->helper->getConfigByGroup('user_model', $group))
            ->get();
        foreach ($userExtends as $userExtend) {
            $this->line("Deleting user: {$userExtend->user->email}");

            if($this->helper->getConfigByGroup('password_policy.auto_delete_inactive_accounts.email_notification.enabled', $group) == true) {
                Mail::send($this->helper->getConfigByGroup('password_policy.auto_delete_inactive_accounts.email_notification.template', $group), [
                    'user' => $userExtend->user,
                    'days' => $days,
                ], function($message) use ($userExtend, $group) {
                    $message->to($userExtend->user->email);
                    $message->subject($this->helper->getConfigByGroup('password_policy.auto_delete_inactive_accounts.email_notification.subject', $group));
                });
            }
            $userExtend->user->delete();
            $userExtend->delete();
        }
    }
}
