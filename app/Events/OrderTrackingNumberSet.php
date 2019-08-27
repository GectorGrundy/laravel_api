<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Order;
use Illuminate\Queue\SerializesModels;

class OrderTrackingNumberSet extends Event
{
    use SerializesModels;

    public $order;

    public function __construct(Order $order)
    {
        $this->order = $order;
    }
}
