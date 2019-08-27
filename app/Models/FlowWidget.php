<?php
declare(strict_types=1);

namespace App\Models;

class FlowWidget extends AbstractEntity
{
    public const IMAGE_PATH = 'storage/images/flow_widgets';
    public const FACEBOOK_PIXEL = 1;
    public const YANDEX_METRIKA = 2;
    public const CUSTOM_CODE = 3;
    public const VK_WIDGET = 4;
    public const RATING_MAIL_RU = 5;
    public const GOOGLE_ANALITYCS = 6;

    protected $fillable = ['title', 'schema'];

    protected $appends = ['thumb_path', 'schema_array'];

    public function getThumbPathAttribute(): string
    {
        return self::IMAGE_PATH . '/' . $this->getAttribute('id') . '.png';
    }

    public function getSchemaArrayAttribute()
    {
        return json_decode($this->getAttribute('schema'), true);
    }
}
