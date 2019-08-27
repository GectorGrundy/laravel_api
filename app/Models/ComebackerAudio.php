<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class ComebackerAudio extends AbstractEntity
{
	use EloquentHashids;
	use DynamicHiddenVisibleTrait;

	public const AUDIO_PATH = '/storage/audio/';

    protected $fillable = ['hash', 'locale_id', 'title'];
    public $table = 'comebacker_audio';
    protected $appends = ['audio_path'];
    public static $rules = [
        'locale_id' => 'required|exists:locales,id',
        'title' => 'required|min:3'
    ];

    public function getAudioPathAttribute(): string
    {
        return $this->getAudioPath();
    }

    public function getAudioPath()
    {
        return self::AUDIO_PATH . $this->getAttribute('hash') . '.mp3';
    }

	public function locale()
	{
		return $this->belongsTo(Locale::class);
	}
}
