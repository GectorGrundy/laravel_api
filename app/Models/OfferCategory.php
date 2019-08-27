<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\HasTranslations;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class OfferCategory extends AbstractEntity
{
    use DynamicHiddenVisibleTrait;
    use HasTranslations;

    protected $fillable = ['title'];
    protected $hidden = ['created_at', 'updated_at', 'deleted_at', 'pivot'];
    protected $dates = ['deleted_at'];

    public function getTitleAttribute($value)
    {
        return $this->getTranslatedAtribute('title', $value);
    }

    public function offers()
    {
        return $this->belongsToMany(Offer::class);
    }

    public function scopeIsAdult(Builder $builder, ?int $is_adult)
    {
        return $builder->when($is_adult, function (Builder $builder) use ($is_adult) {
            return $builder->where('is_adult', $is_adult);
        });
    }

    public function translations()
    {
        return $this->hasMany(OfferCategoryTranslation::class);
    }

    public function syncTitleTranslations(array $data)
    {
        foreach ($data as $item) {
            $translation = $this->translations()->where('locale_id', $item['locale_id'])->firstOrNew([]);
            $translation->locale_id = $item['locale_id'];
            $translation->title = $item['title'];
            $translation->save();
        }
    }
}
