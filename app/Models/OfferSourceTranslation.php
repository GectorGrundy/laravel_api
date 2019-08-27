<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferSourceTranslation extends AbstractEntity
{
    protected $fillable = ['offer_source_id', 'locale_id', 'title'];
    public $timestamps = false;
}
