<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class EmailIntegration extends AbstractEntity
{
    protected $appends = ['extra_array'];

    protected $fillable = [
        'offer_id', 'is_active', 'offer_title', 'mail_from', 'mail_sender', 'mail_driver', 'preorder_template',
        'first_reminder_template', 'success_template', 'extra'
    ];

    public static $rules = [
        'offer_id' => 'required|numeric|exists:offers,id',
        'is_active' => 'required|in:0,1',
        'offer_title' => 'required|string|max:255',
        'mail_from' => 'required|email',
        'mail_sender' => 'required|email',
        'mail_driver' => 'required|string|max:255',
        'preorder_template' => 'required|string|max:255',
        'first_reminder_template' => 'required|string|max:255',
        'success_template' => 'required|string|max:255',
        'extra' => 'required|json',
    ];

    public function getExtraArrayAttribute()
    {
        return $this->attributes['extra_array'] = json_decode($this->attributes['extra'], true);
    }
}
