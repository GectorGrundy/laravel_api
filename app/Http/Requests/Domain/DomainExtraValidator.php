<?php
declare(strict_types=1);

namespace App\Http\Requests\Domain;

use Auth;
use Hashids;
use App\Models\{
    Domain, Flow, Landing, Scopes\GlobalUserEnabledScope, Transit, User
};
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;

/**
 * Дополнительная валидация добавления/редактирования домена
 */
class DomainExtraValidator
{
    /**
     * @var Request
     */
    private $request;
    /**
     * @var Validator
     */
    private $validator;

    public function validate(Request $request, Validator $validator, $action = 'create')
    {
        $this->request = $request;
        $this->validator = $validator;

        $domain = $request->input('domain', '');

        if ($this->isIncorrectDomainFormat($domain)) {
            return $validator->errors()->add('domains', trans('domains.domain.incorrect'));
        }

        $domain = $this->getCleanHost($domain);

        if ($this->isNotUniqueDomain($domain, $action)) {
            return $validator->errors()->add('domain', trans('domains.domain.unique'));
        }

        if (!$this->isValidTypeByUser(Auth::user())) {
            return;
        }

        if (!$this->isValidEntityTypeByUser(Auth::user())) {
            return;
        }
        if (!$this->isValidTypeForEntityType()) {
            return;
        }

        switch ($request->get('type')) {
            case Domain::PARKED_TYPE:
                $this->validateParkedType($domain);
                break;

            case Domain::CUSTOM_TYPE:
                $this->validateCustomType($domain);
                break;

            case Domain::SYSTEM_TYPE:
                $this->validateSystemType($domain);
                break;

        }
    }

    private function isIncorrectDomainFormat($domain): bool
    {
        return empty($domain) || !preg_match('~[\w]+\.[\w]+~', $domain);
    }

    /**
     * Приводим домен в формат example.com
     * @param string $domain
     * @return string
     */
    private function getCleanHost(string $domain): string
    {
        $domain = str_replace(['http://', 'https://', 'www.'], '', $domain);
        $domain = 'http://' . $domain;
        $domain_info = parse_url($domain);

        return $domain_info['host'];
    }

    private function isNotUniqueDomain(string $domain, string $action): bool
    {
        return Domain::withoutGlobalScope(GlobalUserEnabledScope::class)
            ->where('domain', $domain)
            ->when($action === 'edit', function (Builder $builder) {
                $builder->where('hash', '!=', $this->request->input('domain_hash'));
            })
            ->exists();
    }

    private function isValidTypeByUser(User $user): bool
    {
        $type = $this->request->input('type');
        if ($user->isPublisher()) {
            if ($type !== Domain::PARKED_TYPE) {
                $this->validator->errors()->add('type', trans('validation.in', [
                    'attribute' => 'type'
                ]));
                return false;
            }
        } elseif ($user->isAdmin()) {
            if (!\in_array($type, [Domain::CUSTOM_TYPE, Domain::SYSTEM_TYPE])) {
                $this->validator->errors()->add('type', trans('validation.in', [
                    'attribute' => 'type'
                ]));
                return false;
            }
        }

        return true;
    }

    private function isValidEntityTypeByUser(User $user): bool
    {
        $entity_type = $this->request->input('entity_type');
        if ($user->isPublisher()) {
            if ($entity_type !== Domain::FLOW_ENTITY_TYPE) {
                $this->validator->errors()->add('type', trans('validation.in', [
                    'attribute' => 'entity_type'
                ]));
                return false;
            }
        } elseif ($user->isAdmin()) {
            if ($entity_type === Domain::FLOW_ENTITY_TYPE) {
                $this->validator->errors()->add('type', trans('validation.in', [
                    'attribute' => 'type'
                ]));
                return false;
            }
        }

        return true;
    }

    private function isValidTypeForEntityType(): bool
    {
        $type = $this->request->input('type');
        $entity_type = $this->request->input('entity_type');

        switch ($type) {
            case Domain::CUSTOM_TYPE:
                if (!\in_array($entity_type, [Domain::LANDING_ENTITY_TYPE, Domain::TRANSIT_ENTITY_TYPE])) {
                    $this->validator->errors()->add('type', trans('validation.in', [
                        'attribute' => 'entity_type'
                    ]));
                    return false;
                }
                break;

            case Domain::PARKED_TYPE:
                if ($entity_type !== Domain::FLOW_ENTITY_TYPE) {
                    $this->validator->errors()->add('type', trans('validation.in', [
                        'attribute' => 'entity_type'
                    ]));
                    return false;
                }
                break;

            case Domain::SYSTEM_TYPE:
                if (!\in_array($entity_type, [Domain::TDS_ENTITY_TYPE, Domain::REDIRECT_ENTITY_TYPE])) {
                    $this->validator->errors()->add('type', trans('validation.in', [
                        'attribute' => 'entity_type'
                    ]));
                    return false;
                }
                break;
        }

        return true;
    }

    private function validateParkedType(string $domain): void
    {
        // Попытка припарковать поддомен/домен кабинетов
        if (str_contains($domain, config('env.main_domain'))) {
            $this->validator->errors()->add('domain', trans('domains.domain.service_cant_park'));
            return;
        }

        // Попытка припарковать сервисный поддомен/домен
        Domain::service()->get()->each(function ($item) use ($domain) {
            if (str_contains($domain, $item->domain)) {
                return $this->validator->errors()->add('domain', trans('domains.domain.service_cant_park'));
            }
        });

        // Для доменов с новым клоакингом не задаем поток
        if (!empty($this->request->input('donor_url'))) {
            $flow_id = 0;
            $is_public = 0;
        } else {
            $flow_id = Hashids::decode($this->request->input('fallback_flow_hash'))[0] ?? 0;
            try {
                Flow::findOrFail($flow_id);
            } catch (ModelNotFoundException $e) {
                $this->validator->errors()->add('fallback_flow_hash', trans('domains.flow.incorrect'));
                return;
            }
        }

        $this->request->merge([
            'domain' => $domain,
            'is_public' => $is_public ?? $this->request->input('is_public'),
            'fallback_flow_id' => $flow_id ?? 0,
            'entity_id' => 0,
            'user_id' => $this->request->get('type') === Domain::PARKED_TYPE ? \Auth::id() : 0,
            'realpath' => '',
            'is_active' => 1,
        ]);
    }

    private function validateCustomType(string $domain): void
    {
        $entity_id = Hashids::decode($this->request->input('entity_hash', ''))[0] ?? 0;
        if (!$entity_id) {
            $this->validator->errors()->add('entity_hash', trans('validation.in', [
                'attribute' => 'entity_hash'
            ]));
            return;
        }

        if ($this->request->get('entity_type') === Domain::TRANSIT_ENTITY_TYPE) {
            $entity_system_domain = Transit::with(['system_domain'])->find($entity_id)->system_domain;
        } else {
            $entity_system_domain = Landing::with(['system_domain'])->find($entity_id)->system_domain;
        }

        $this->request->merge([
            'domain' => $domain,
            'is_public' => $this->request->input('is_public'),
            'fallback_flow_id' => 0,
            'entity_id' => $entity_id,
            'user_id' => $this->request->get('type') === Domain::PARKED_TYPE ? \Auth::id() : 0,
            'realpath' => $entity_system_domain->realpath,
            'is_active' => 1,
        ]);
    }

    private function validateSystemType(string $domain): void
    {
        $this->request->merge([
            'domain' => $domain,
            'is_public' => 1,
            'fallback_flow_id' => 0,
            'entity_id' => 0,
            'user_id' => $this->request->get('type') === Domain::PARKED_TYPE ? \Auth::id() : 0,
            'realpath' => '',
            'is_active' => $this->request->get('is_active', 1),
        ]);
    }
}
