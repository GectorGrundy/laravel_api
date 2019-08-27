<?php
declare(strict_types=1);

namespace App\Listeners\Auth;

use App\Events\Auth\Login;
use App\Models\AuthLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class LogSuccessfulLogin implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function handle(Login $event): void
    {
        AuthLog::create([
            'user_id' => $event->user->id,
            'user_agent' => request()->header('User-Agent'),
            'ip' => request()->ip()
        ]);
    }
}
