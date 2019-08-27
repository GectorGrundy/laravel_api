<?php
declare(strict_types=1);

namespace App\Models;

use DB;
use Carbon\Carbon;
use App\Http\Middleware\Timezones;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

/**
 * @mixin \Eloquent
 * @method $this findOrFail(int $id)
 * @method $this createdBetweenDates(?string $date_from, ?string $date_to)
 * @method $this datetimeBetweenDates(?string $datetime_from, ?string $datetime_to)
 * @method $this datetimeBetweenDatetimes(?string $datetime_from, ?string $datetime_to)
 * @method $this processedBetweenDates(?string $date_from, ?string $date_to)
 * @method $this createdTo(?string $created_to_datetime)
 * @method $this createdFrom(?string $created_from_datetime)
 * @method $this createdAtHour(?string $hour)
 * @method $this datetimeDate(?string $date)
 */
class AbstractEntity extends Model
{
    public const ON_DUPLICATE_KEY_ERROR_CODE = 23000;
    private const SYSTEM_UTC_OFFSET = 3;

    protected function getCreatedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    protected function getUpdatedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    protected function getDeletedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    protected function getProcessedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // HourlyStat, DeviceStat models
    protected function getDatetimeAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // Lead model
    protected function getAdvertiserPayoutCompletedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // Lead model
    protected function getInitializedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // News model
    protected function getPublishedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // Ticket model
    protected function getLastMessageAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // AuthToken model
    protected function getLastActivityAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // FailedJob model
    protected function getFailedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // FailedJob model
    protected function getReservedAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // FailedJob model
    protected function getAvailableAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    // Lead model
    protected function getHoldAtAttribute($value)
    {
        return $this->getDatefieldInUsetTz($value);
    }

    protected function getDatefieldInUsetTz($value)
    {
        if (\is_null($value)) {
            return $value;
        }

        [$app_tz, $user_tz] = $this->getTzs();

        if (!$value instanceof Carbon) {
            $value = Carbon::createFromFormat('Y-m-d H:i:s', $value, $app_tz);
        }

        return $value->subHours(self::SYSTEM_UTC_OFFSET)->addHours($user_tz)->toDateTimeString();
    }

    public function scopeCreatedBetweenDates(Builder $builder, $date_from, $date_to)
    {
        if (empty($date_from)) {
            $date_from = Carbon::now()->subDays(7)->toDateTimeString();
        }

        if (empty($date_to)) {
            $date_to = Carbon::now()->toDateTimeString();
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("DATE(CONVERT_TZ({$table}.created_at, '{$app_tz}', '{$user_tz}'))"),
                '>=',
                $date_from
            )
            ->where(DB::raw("DATE(CONVERT_TZ({$table}.created_at, '{$app_tz}', '{$user_tz}'))"),
                '<=',
                $date_to
            );
    }

    public function scopeDatetimeBetweenDates(Builder $builder, $date_from, $date_to)
    {
        if (empty($date_from)) {
            $date_from = Carbon::now()->subDays(7)->toDateString();
        }

        if (empty($date_to)) {
            $date_to = Carbon::now()->toDateString();
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("DATE(CONVERT_TZ({$table}.datetime, '{$app_tz}', '{$user_tz}'))"),
                '>=',
                $date_from
            )
            ->where(DB::raw("DATE(CONVERT_TZ({$table}.datetime, '{$app_tz}', '{$user_tz}'))"),
                '<=',
                $date_to
            );
    }

    public function scopeDatetimeBetweenDatetimes(Builder $builder, $datetime_from, $datetime_to)
    {
        if (empty($datetime_from)) {
            $datetime_from = Carbon::now()->subDays(7)->toDateString();
        }

        if (empty($datetime_to)) {
            $datetime_to = Carbon::now()->toDateString();
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("CONVERT_TZ({$table}.datetime, '{$app_tz}', '{$user_tz}')"),
                '>=',
                $datetime_from
            )
            ->where(DB::raw("CONVERT_TZ({$table}.datetime, '{$app_tz}', '{$user_tz}')"),
                '<=',
                $datetime_to
            );
    }

    public function scopeDatetimeDate(Builder $builder, $date)
    {
        if (empty($date)) {
            return $builder;
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("DATE(CONVERT_TZ({$table}.datetime, '{$app_tz}', '{$user_tz}'))"),
                '=',
                $date
            );
    }

    public function scopeDatetimeHour(Builder $builder, $date)
    {
        if (empty($date)) {
            return $builder;
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("HOUR(CONVERT_TZ({$table}.datetime, '{$app_tz}', '{$user_tz}'))"),
                '=',
                $date
            );
    }

    public function scopeWeekDay(Builder $builder, $date)
    {
        if (empty($date)) {
            return $builder;
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("WEEKDAY(CONVERT_TZ({$table}.datetime, '{$app_tz}', '{$user_tz}'))"),
                '=',
                $date
            );
    }

    public function scopeProcessedBetweenDates(Builder $builder, $date_from, $date_to)
    {
        if ($date_from === '0') {
            return $builder->whereNull('processed_at');
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        if (!empty($date_from)) {
            $builder->where(
                DB::raw("DATE(CONVERT_TZ({$table}.processed_at, '{$app_tz}', '{$user_tz}'))"),
                '>=',
                $date_from);
        }

        if (!empty($date_to)) {
            $builder->where(
                DB::raw("DATE(CONVERT_TZ({$table}.processed_at, '{$app_tz}', '{$user_tz}'))"),
                '<=',
                $date_to
            );
        }

        return $builder;
    }

    public function scopeCreatedTo(Builder $builder, $created_to_datetime)
    {
        if (empty($created_to_datetime)) {
            $created_to_datetime = Carbon::now()->toDateTimeString();
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("CONVERT_TZ({$table}.created_at, '{$app_tz}', '{$user_tz}')"),
                '<=',
                $created_to_datetime
            );
    }

    public function scopeCreatedFrom(Builder $builder, $created_from_datetime)
    {
        if (empty($created_from_datetime)) {
            $created_from_datetime = Carbon::now()->toDateTimeString();
        }

        $table = $this->getTable();
        [$app_tz, $user_tz] = $this->getTzs();

        return $builder
            ->where(DB::raw("CONVERT_TZ({$table}.created_at, '{$app_tz}', '{$user_tz}')"),
                '>=',
                $created_from_datetime
            );
    }

    public function scopeCreatedAtHour(Builder $builder, $hour)
    {
        if (empty($hour)) {
            return $builder;
        }

        $table = $this->getTable();

        [$app_tz, $user_tz] = $this->getTzs();

        return $builder->where(DB::raw("HOUR(CONVERT_TZ({$table}.created_at, '{$app_tz}', '{$user_tz}'))"), $hour);
    }

    /**
     * Returns app and user timezones offset in format "+03:00" (e.g.)
     *
     * @return array
     */
    public function getTzs()
    {
        $tz = app(Timezones::TZ_OFFSETS);
        return [$tz['app'], $tz['user']];
    }
}
