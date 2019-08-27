<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Model;

class OfferSource extends AbstractEntity
{
    use HasTranslations;

    protected $fillable = ['title'];
    protected $hidden = ['pivot'];
    public $timestamps = false;

    public function getTitleAttribute($value)
    {
        return $this->getTranslatedAtribute('title', $value);
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class);
    }

    public function translations()
    {
        return $this->hasMany(OfferSourceTranslation::class);
    }
}
