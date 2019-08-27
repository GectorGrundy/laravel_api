<?php
declare(strict_types=1);

namespace App\Models;

class TargetTemplateTranslation extends AbstractEntity
{
    protected $fillable = ['target_template_id', 'locale_id', 'title'];
    public $timestamps = false;
}
