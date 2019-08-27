<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\Payment;
use Illuminate\Queue\SerializesModels;

class PaymentPaid extends Event
{
    use SerializesModels;

    public $payment;

    public function __construct(Payment $payment)
    {
        $this->payment = $payment;
    }
}
