<?php

namespace App\Listeners;

use App\Events\PaymentPaid;

class PaymentRequisiteLockAfterFirstPayment
{
    public function handle(PaymentPaid $event)
    {
        $requisite = $event->payment->requisite;

        if ($requisite->is_editable) {
            $requisite->disallowEdit();
        }
    }
}
