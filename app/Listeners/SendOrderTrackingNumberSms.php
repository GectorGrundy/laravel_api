<?php
declare(strict_types=1);

namespace App\Listeners;

use App\Events\OrderTrackingNumberSet;
use App\Factories\SmsIntegrationFactory;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;

class SendOrderTrackingNumberSms implements ShouldQueue
{
    use Queueable;
    use SerializesModels;

    public function handle(OrderTrackingNumberSet $event)
    {
        if ($event->order->is_tracking_number_sms_notified) {
            return;
        }

        $job = SmsIntegrationFactory::getInstance(
            $event->order->lead->offer->sms_integration['title'],
            $event->order->id,
            $event->order->lead->offer->sms_integration['id']
        );
        $job->onQueue(config('queue.app.email'));
        dispatch($job);
    }
}
