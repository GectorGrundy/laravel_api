<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferCategoryTranslation extends AbstractEntity
{
    protected $fillable = ['offer_category_id', 'locale_id', 'title'];
    public $timestamps = false;
}
