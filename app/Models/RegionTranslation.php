<?php
declare(strict_types=1);

namespace App\Models;

class RegionTranslation extends AbstractEntity
{
	protected $fillable = ['region_id', 'locale_id', 'title'];
	public $timestamps = false;
}
