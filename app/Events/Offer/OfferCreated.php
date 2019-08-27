<?php
declare(strict_types=1);

namespace App\Events\Offer;

use App\Events\Event;
use App\Models\Offer;
use Illuminate\Queue\SerializesModels;

class OfferCreated extends Event
{
    use SerializesModels;

    public $offer;

    public function __construct(Offer $offer)
    {
        $this->offer = $offer;
    }
}
