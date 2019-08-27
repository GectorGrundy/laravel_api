<?php
declare(strict_types=1);

namespace App\Events;

use App\Models\MyOffer;
use App\Contracts\UserActivityEntity;
use Illuminate\Queue\SerializesModels;

class MyOfferCreated extends Event implements UserActivityEntity
{
    use SerializesModels;

    public $my_offer;

    public function __construct(MyOffer $my_offer)
    {
        $this->my_offer = $my_offer;
    }

    public function getUserId(): int
    {
        return $this->my_offer['publisher_id'];
    }

    public function getEntityId(): int
    {
        return $this->my_offer['id'];
    }

    public function getEntityType(): string
    {
        return 'my_offer';
    }
}
