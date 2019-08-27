<?php
declare(strict_types=1);

namespace App\Integrations\Turbosms;

use App\Models\{
    SmsIntegration, Order
};
use Illuminate\Bus\Queueable;
use Illuminate\Queue\{
    SerializesModels, InteractsWithQueue
};
use Illuminate\Contracts\Queue\ShouldQueue;

class TurbosmsSendOrderTrackNumberSms implements ShouldQueue
{
    use Queueable;
    use InteractsWithQueue;
    use SerializesModels;

    private $order_id;
    private $sms_integration_id;

    public function __construct(int $order_id, int $sms_integration_id)
    {
        $this->order_id = $order_id;
        $this->sms_integration_id = $sms_integration_id;
    }

    public function handle()
    {
        $this->lead = Order::findOrFail($this->order_id);
        $this->sms_integration = SmsIntegration::findOrFail($this->sms_integration_id);
    }
}
