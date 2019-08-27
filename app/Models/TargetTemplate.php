<?php
declare(strict_types=1);

namespace App\Models;

use App\Models\Traits\DynamicHiddenVisibleTrait;
use App\Models\Traits\HasTranslations;
use Illuminate\Database\Eloquent\SoftDeletes;

class TargetTemplate extends AbstractEntity
{
    use HasTranslations;
    use SoftDeletes;
    use DynamicHiddenVisibleTrait;

    protected $fillable = ['title'];
    protected $hidden = ['id', 'deleted_at'];
    public $timestamps = false;

    public static $rules = [
        'title' => 'required|string|max:255',
    ];

    public function getTitleAttribute($value)
    {
        return $this->getTranslatedAtribute('title', $value);
    }

    public function translations()
    {
        return $this->hasMany(TargetTemplateTranslation::class);
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
