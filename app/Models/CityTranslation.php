<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CityTranslation extends AbstractEntity
{
	protected $fillable = ['city_id', 'locale_id', 'title'];
	public $timestamps = false;
}