<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\{
    User, AdvertiserProfile, PublisherProfile
};

class UserBalanceRecovery extends Command
{
    protected $signature = 'user:recovery_balance {user_id}';
    protected $description = 'Recovery user balance by balance transactions';

    public function handle()
    {
        $user = (new User())->getById((int)$this->argument('user_id'));

        switch ($user['role']) {
            case 'advertiser':
                (new AdvertiserProfile())->restoreBalanceByBalanceTransactions($user['id']);
                break;

            case 'publisher':
                (new PublisherProfile())->restoreBalanceByBalanceTransactions($user['id']);
                break;
        }

        $this->info("\n ( ͡° ͜ʖ ͡°) \n");
    }
}
