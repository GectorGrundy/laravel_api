<?php
declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;

class TicketMessage extends AbstractEntity
{
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    protected $fillable = ['ticket_id', 'user_id', 'user_type', 'message'];
    protected $hidden = ['id', 'ticket_id', 'user_id', 'updated_at'];

    public function setMessageAttribute($value)
    {
        $this->attributes['message'] = nl2br($value);
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class);
    }

    public function user()
    {
        return $this->morphTo();
    }

    public static function createNew(User $user, Ticket $ticket, string $message)
    {
        return self::create([
            'user_id' => $user['id'],
            'user_type' => $user['role'],
            'ticket_id' => $ticket['id'],
            'message' => $message
        ]);
    }
}
