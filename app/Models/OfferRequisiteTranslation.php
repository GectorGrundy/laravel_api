<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OfferRequisiteTranslation extends AbstractEntity
{
	protected $fillable = ['offer_id', 'locale_id', 'content'];

	public $timestamps = false;

	protected $table = 'offer_requisite_translation';

	public function locale()
	{
		return $this->belongsTo(Locale::class);
	}
}
