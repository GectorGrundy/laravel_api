<?php
declare(strict_types=1);

namespace App\Models\Traits;

use Illuminate\Database\Eloquent\Model;
use Hashids;

trait EloquentHashids
{
    /**
     * Boot Eloquent Hashids trait for the model.
     *
     * @return void
     */
    public static function bootEloquentHashids()
    {
        static::created(function (Model $model) {

            $model->{static::getHashidColumn($model)} = Hashids::connection(
                static::getHashidConnection($model))->encode(static::getHashidEncodingValue($model)
            );

            $model->save();
        });
    }

    public static function getHashidConnection(Model $model)
    {
        return 'main';
    }

    public static function getHashidColumn(Model $model)
    {
        return 'hash';
    }

    public static function getHashidEncodingValue(Model $model)
    {
        return $model['id'];
    }
}
