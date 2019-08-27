<?php
declare(strict_types=1);

namespace App\Models\Traits;

use Auth;
use App\Models\User;

trait DynamicHiddenVisibleTrait
{
    public function toArray()
    {
        if (!isApiRequest() || (!\is_null(Auth::user()) && Auth::user()->isAdmin())) {
            // Исключаем поля, которые не важны для администратора и которые есть в скрытых
            $hidden = collect($this->getHidden())->reject(function ($field) {
                return \in_array($field, $this->getAdminHiddenFields());
            })->toArray();

            $this->makeVisible($hidden);
            return parent::toArray();
        }

        switch (\Auth::user()['role']) {
            case User::ADVERTISER:
                if ($this->advertiser_hidden !== null) {
                    $this->setHidden($this->advertiser_hidden);
                }
                return parent::toArray();

            case User::PUBLISHER:
                return parent::toArray();

            default:
                return parent::toArray();
        }
    }

    private function getAdminHiddenFields()
    {
        return [
            'today_epc', 'yesterday_epc', 'week_epc', 'month_epc', 'today_cr',
            'yesterday_cr', 'week_cr', 'month_cr',
            'remember_token', 'password', 'created_at', 'updated_at', 'deleted_at'
        ];
    }
}
