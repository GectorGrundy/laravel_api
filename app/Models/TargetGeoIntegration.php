<?php
declare(strict_types=1);

namespace App\Models;

class TargetGeoIntegration extends AbstractEntity
{
    public const API = 'api';
    public const REDIRECT = 'redirect';

    protected $fillable = ['advertiser_id', 'target_geo_id', 'charge', 'currency_id', 'integration_type'];
    protected $hidden = ['created_at', 'updated_at'];
    public static $rules = [
        'advertiser_id' => 'required|exists:users,id,role,' . User::ADVERTISER,
        'charge' => 'required|numeric|min:0.01',
        'integration_type' => 'required|in:' . self::API . ',' . self::REDIRECT,
    ];

    public function advertiser()
    {
        return $this->belongsTo(User::class, 'advertiser_id');
    }
}
