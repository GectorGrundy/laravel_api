<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TargetGeoRegion extends AbstractEntity
{
    protected $fillable = ['country_id', 'title', 'tax', 'tax_percent'];
    public $timestamps = false;

    public static function getById(int $id): self
    {
        return self::findOrFail($id);
    }

    public static function getByCountryId(int $country_id): self
    {
        return self::where('country_id', $country_id)->get();
    }
}
