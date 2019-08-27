<?php
declare(strict_types=1);

namespace App\Models\Traits;

use App\Models\Locale;
use Illuminate\Database\Eloquent\Builder;

/**
 * @method $this translate() Use this method if need to translate model title
 */
trait HasTranslations
{
    private static $locale_id = 0;
    private static $remove_translations_relation = true;

    public function getTranslatedAtribute(string $field, ?string $default)
    {
        if (self::$locale_id === 0) {
            return $default;
        }

        $value = $this->getTranslationForLocale(self::$locale_id)[$field] ?? $default;

        if (self::$remove_translations_relation && $this->relationLoaded('translations')) {
            $this->setHidden(['translations']);
        }

        return $value;
    }

    public function scopeTranslate(Builder $builder, int $locale_id = 0)
    {
        self::$locale_id = $locale_id ?: request()->input('locale_info')['id'] ?? Locale::EN;

        if (array_key_exists('translations', $builder->getEagerLoads())) {
            self::$remove_translations_relation = false;
        }

        return $builder->with(['translations']);
    }

    private function getTranslationForLocale($locale_id)
    {
        return $this->translations->where('locale_id', $locale_id)->first();
    }
}
