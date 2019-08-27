<?php
declare(strict_types=1);

namespace App\Models;

class OfferTranslation extends AbstractEntity
{
    protected $fillable = ['offer_id', 'locale_id', 'title', 'description', 'agreement'];
    public $timestamps = false;
}
