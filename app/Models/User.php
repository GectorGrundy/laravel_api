<?php
declare(strict_types=1);

namespace App\Models;

use App\Exceptions\User\UnknownUserRole;
use App\Mail\ResetPassword;
use App\Models\Scopes\GlobalUserEnabledScope;
use DateTimeZone;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Notifications\Notifiable;
use App\Models\Traits\EloquentHashids;
use App\Models\Traits\DynamicHiddenVisibleTrait;
use Illuminate\Auth\Authenticatable;
use Illuminate\Auth\Passwords\CanResetPassword;
use Illuminate\Foundation\Auth\Access\Authorizable;
use Illuminate\Contracts\Auth\Authenticatable as AuthenticatableContract;
use Illuminate\Contracts\Auth\Access\Authorizable as AuthorizableContract;
use Illuminate\Contracts\Auth\CanResetPassword as CanResetPasswordContract;

/**
 * @mixin AbstractEntity
 * @property AdvertiserProfile|PublisherProfile $profile
 * @method $this search(?string $search_field, ?string $search)
 * @method $this whereRoles(array $roles)
 */
class User extends AbstractEntity implements
    AuthenticatableContract,
    AuthorizableContract,
    CanResetPasswordContract
{
    use EloquentHashids;
    use DynamicHiddenVisibleTrait;
    use Notifiable;
    use Authenticatable;
    use Authorizable;
    use CanResetPassword;

    public const ADMINISTRATOR = 'administrator';
    public const PUBLISHER = 'publisher';
    public const ADVERTISER = 'advertiser';
    public const SUPPORT = 'support';
    public const MANAGER = 'manager';

    public const MOSCOW_TZ = 'Europe/Moscow';
    public const KIEV_TZ = 'Europe/Kiev';

    public const LOCKED = 'locked';
    public const ACTIVE = 'active';

    protected $table = 'users';
    protected $fillable = [
        'email', 'nickname', 'role', 'status', 'locale', 'timezone', 'group_id', 'reason_for_blocking', 'password',
        'unread_news_count'
    ];
    protected $hidden = [
        'id', 'group_id', 'password', 'remember_token', 'deleted_at', 'updated_at', 'pivot'
    ];
    protected $dates = ['created_at', 'updated_at', 'deleted_at'];
    // Need for profile method
    public static $role;

    public static function getHashidEncodingValue(Model $model)
    {
        return $model->id;
    }

    public function setPasswordAttribute(string $value)
    {
        // Crutch because of ResetsPasswords@resetPassword
        if (!starts_with($value, '$2y$10$')) {
            $value = bcrypt($value);
        }

        $this->attributes['password'] = $value;
    }

    public function setEmailAttribute(string $value)
    {
        $this->attributes['nickname'] = explode('@', $value)[0];
        $this->attributes['email'] = strtolower($value);
    }

    public static function boot()
    {
        parent::boot();

        static::addGlobalScope(new GlobalUserEnabledScope);
    }

    public function publisher()
    {
        return $this->hasOne(PublisherProfile::class, 'user_id', 'id');
    }

    public function flows()
    {
        return $this->hasMany(Flow::class);
    }

    public function administrator()
    {
        return $this->hasOne(AdministratorProfile::class, 'user_id', 'id');
    }

    public function advertiser()
    {
        return $this->hasOne(AdvertiserProfile::class, 'user_id', 'id');
    }

    public function support()
    {
        return $this->hasOne(SupportProfile::class, 'user_id', 'id');
    }

    public function manager()
    {
        return $this->hasOne(ManagerProfile::class, 'user_id', 'id');
    }

    public function profile()
    {
        switch (self::$role) {
            case self::PUBLISHER:
                return $this->hasOne(PublisherProfile::class, 'user_id', 'id');

            case self::ADMINISTRATOR:
                return $this->hasOne(AdministratorProfile::class, 'user_id', 'id');

            case self::ADVERTISER:
                return $this->hasOne(AdvertiserProfile::class, 'user_id', 'id');

            case self::SUPPORT:
                return $this->hasOne(SupportProfile::class, 'user_id', 'id');

            case self::MANAGER:
                return $this->hasOne(ManagerProfile::class, 'user_id', 'id');

            default:
                throw new \BadMethodCallException('Unknown user role.');
        }
    }

    public function wmr()
    {
        return $this->hasOne(WebmoneyRequisite::class, 'user_id', 'id')
            ->where('payment_system_id', PaymentSystem::WEBMONEY_RUB);
    }

    public function wmz()
    {
        return $this->hasOne(WebmoneyRequisite::class, 'user_id', 'id')
            ->where('payment_system_id', PaymentSystem::WEBMONEY_USD);
    }

    public function wme()
    {
        return $this->hasOne(WebmoneyRequisite::class, 'user_id', 'id')
            ->where('payment_system_id', PaymentSystem::WEBMONEY_EUR);
    }

    public function paxum_requsites()
    {
        return $this->hasMany(PaxumRequisite::class, 'user_id', 'id');
    }

    public function epayments_requsites()
    {
        return $this->hasMany(EpaymentsRequisite::class, 'user_id', 'id');
    }

    public function swift_requisites()
    {
        return $this->hasMany(SwiftRequisite::class, 'user_id', 'id');
    }

    public function group()
    {
        return $this->belongsTo(UserGroup::class, 'group_id');
    }

    public function scopeUserEnabled(Builder $builder): Builder
    {
        $user = \Auth::user();
        switch ($user['role']) {
            case self::ADVERTISER:
                // @todo с этим условием не можем получить паблишера в при получении лидов,
                // хэши которых выводятся в отчетах
                // $builder->where('id', $user['id']);
                break;

            case self::PUBLISHER:
                $builder->where('id', $user['id']);
                break;

            case self::SUPPORT:
                $user_ids = PublisherProfile::where('support_id', $user['id'])
                    ->get(['user_id'])
                    ->pluck('user_id')->toArray();
                $user_ids[] = $user['id'];
                $builder->whereIn('users.id', $user_ids);
                break;

            case self::MANAGER:
                $user_ids = AdvertiserProfile::where('manager_id', $user['id'])
                    ->get(['user_id'])
                    ->pluck('user_id')->toArray();
                $user_ids[] = $user['id'];
                $builder->whereIn('users.id', $user_ids);
                break;

            case self::ADMINISTRATOR:
                break;

            default:
                throw new UnknownUserRole($user['role']);
        }

        return $builder;
    }

    public function scopeWhereRoles(Builder $builder, array $roles)
    {
        if (\count($roles)) {
            $builder->whereIn('role', $roles);
        }
        return $builder;
    }

    public function scopeSearch(Builder $builder, ?string $search_field, ?string $search)
    {
        if (empty($search_field) || empty($search)) {
            return $builder;
        }

        switch ($search_field) {
            case 'email':
                /**
                 * @var UserElasticQueries $queries
                 */
                $queries = app(UserElasticQueries::class);
                $user_ids = $queries->findIdsByEmail($search);

                $builder->whereIn('users.id', $user_ids);
                break;
        }

        return $builder;
    }

    public function scopeHasGroup(Builder $builder)
    {
        return $builder->where('group_id', '!=', 0);
    }

    public function getById(int $id): User
    {
        return self::findOrFail($id);
    }

    public static function getByEmail(string $email): User
    {
        return self::whereEmail($email)->firstOrFail();
    }

    public function sendPasswordResetNotification($token)
    {
        $this->notify(new ResetPassword($token));
    }

    public function isAdmin()
    {
        return $this->role === self::ADMINISTRATOR;
    }

    public function isPublisher()
    {
        return $this->role === self::PUBLISHER;
    }

    public function isAdvertiser()
    {
        return $this->role === self::ADVERTISER;
    }

    public function isSupport()
    {
        return $this->role === self::SUPPORT;
    }

    public function isManager()
    {
        return $this->role === self::MANAGER;
    }

    public static function generateNickname(string $email)
    {
        return explode('@', $email)[0];
    }

    public static function incrementUnreadNewsCounter()
    {
        self::where('role', '!=', self::ADMINISTRATOR)->increment('unread_news_count');
    }

    public static function resetUnreadNewsCounter(User $user)
    {
        self::where('id', $user['id'])->update(['unread_news_count' => 0]);
    }

    public function isFallbackPublisher()
    {
        return $this['id'] === (int)config('env.fallback_publisher_id');
    }

    public function updateTimezone(string $tz)
    {
        $this->update([
            'timezone' => $tz,
        ]);
    }

    public function publisherBoundToSupport(string $publisher_hash)
    {
        return self::where('role', self::PUBLISHER)->where('hash', $publisher_hash)->exists();
    }

    public function advertiserBoundToManager(string $publisher_hash)
    {
        return self::where('role', self::ADVERTISER)->where('hash', $publisher_hash)->exists();
    }
}
