<?php
declare(strict_types=1);

namespace App\Models;

use App\Events\MyOfferCreated;

class MyOffer extends AbstractEntity
{
    protected $fillable = ['publisher_id', 'offer_id'];
    public $timestamps = false;

    public static function createNew(int $offer_id, int $publisher_id): self
    {
        $my_offer = self::create([
            'offer_id' => $offer_id,
            'publisher_id' => $publisher_id
        ]);

        event(new MyOfferCreated($my_offer));

        return $my_offer;
    }
}
