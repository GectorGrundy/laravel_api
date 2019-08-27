<?php
declare(strict_types=1);

namespace App\Models;

use App\Exceptions\User\UnknownUserRole;
use App\Models\Scopes\GlobalUserEnabledScope;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;

/**
 * @method $this active()
 * @method $this closed()
 */
class Ticket extends AbstractEntity
{
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;

    public const CREATED = 'created';
    public const PENDING_ANSWER = 'pending_answer';
    public const PENDING_REACTION = 'pending_reaction';
    public const DEFERRED = 'deferred';
    public const CLOSED = 'closed';

    protected $fillable = [
        'title', 'user_id', 'last_message_user_id', 'last_message_user_type', 'responsible_user_id', 'status',
        'admin_messages_count', 'deferred_until_at', 'is_read_user', 'is_read_admin', 'last_message_at',
    ];
    protected $hidden = [
        'id', 'user_id', 'last_message_user_id', 'responsible_user_id', 'deferred_until_at', 'responsible_user',
        'is_read_admin', 'updated_at'
    ];
    protected $dates = ['last_message_at', 'deferred_until_at'];

    public static $allowed_publisher_relations = ['last_message_user.profile'];

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function messages()
    {
        return $this->hasMany(TicketMessage::class)->latest('id');
    }

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function responsible_user()
    {
        return $this->belongsTo(User::class, 'responsible_user_id');
    }

    public function last_message_user()
    {
        return $this->morphTo();
    }

    public function scopeUserEnabled(Builder $query)
    {
        $user = \Auth::user();
        if (!$user->isAdmin()) {
            $query->where('user_id', $user['id']);
        }

        return $query;
    }

    public function scopeClosed(Builder $builder)
    {
        return $builder->where('status', self::CLOSED);
    }

    public function scopeActive(Builder $builder)
    {
        return $builder->where('status', '!=', self::CLOSED);
    }

    public static function getReadFieldByUserRole(string $user_role): string
    {
        switch ($user_role) {
            case User::ADMINISTRATOR:
                return 'is_read_admin';

            case User::PUBLISHER:
                return 'is_read_user';

            default:
                throw new UnknownUserRole($user_role);
        }
    }

    public function updateOnNewMessage(User $user)
    {
        $upd_fields = $this->getReadFlags($user);

        $upd_fields['last_message_user_id'] = $user['id'];
        $upd_fields['last_message_user_type'] = $user['role'];
        $upd_fields['last_message_at'] = Carbon::now()->toDateTimeString();

        if ($user->isAdmin()) {
            $upd_fields['admin_messages_count'] = \DB::raw('`admin_messages_count` + 1');
        }

        $this->update($upd_fields);

        return $this;
    }

    public function getReadFlags(User $message_user): array
    {
        // Is it publisher sent message?
        if ($message_user['id'] === $this['user_id']) {
            $fields = [
                'is_read_admin' => 0,
                'is_read_user' => 1,
            ];
        } else {
            $fields = [
                'is_read_admin' => 1,
                'is_read_user' => 0,
            ];
        }

        return $fields;
    }
}
