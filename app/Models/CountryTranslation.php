<?php
declare(strict_types=1);

namespace App\Models;

class CountryTranslation extends AbstractEntity
{
	protected $fillable = ['country_id', 'locale_id', 'title'];

	public function scopeWhereLocale($query, $locale_id)
	{
		if ($locale_id != 0) {
			$query->where('locale_id', $locale_id);
		}

		return $query;
	}
}
