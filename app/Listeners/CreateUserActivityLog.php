<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Contracts\UserActivityEntity;
use App\Models\UserActivityLog;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class CreateUserActivityLog implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function handle(UserActivityEntity $event)
    {
        UserActivityLog::create([
            'user_id' => $event->getUserId(),
            'entity_id' => $event->getEntityId(),
            'entity_type' => $event->getEntityType(),
            'user_agent' => request()->header('User-Agent'),
            'ip' => request()->ip(),
            'request' => serialize(request()->except('request_user', 'locale_info'))
        ]);
    }
}
