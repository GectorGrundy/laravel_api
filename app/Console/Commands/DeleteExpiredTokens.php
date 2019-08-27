<?php
declare(strict_types=1);

namespace App\Console\Commands;

use Carbon\Carbon;
use App\Models\AuthToken;
use Illuminate\Console\Command;

class DeleteExpiredTokens extends Command
{
    protected $signature = 'auth_tokens:delete_expired';
    protected $description = 'Delete expired tokens';

    public function handle()
    {
        $remember_ttl_min = config('jwt.remember_ttl');
        $expired_at = Carbon::now()->subMinutes($remember_ttl_min)->toDateTimeString();

        AuthToken::where('last_activity', '<=', $expired_at)->delete();
    }
}
