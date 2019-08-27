<?php
declare(strict_types=1);

namespace App\Http\Requests\CloakDomainPath;

use App\Models\Domain;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;

class PathValidator
{
    public function validate(Request $request, Validator $validator, $action)
    {
        $domain = Domain::where('hash', $request->input('domain_hash'))
            ->entityTypes([Domain::FLOW_ENTITY_TYPE])
            ->first();

        if (\is_null($domain)) {
            return $validator->errors()->add('domain_hash', trans('validation.exists', [
                'attribute' => 'domain_hash'
            ]));
        }

        switch ($action) {
            case 'create':
                $rules = [
                    'path' => 'required|string|unique:cloak_domain_paths,path,NULL,id,domain_id,' . $domain->id . ',deleted_at,NULL',
                ];
                break;

            case 'edit':
                $rules = [
                    'path' => 'required|string|unique:cloak_domain_paths,path,' . $request->hash . ',hash,domain_id,' . $domain->id . ',deleted_at,NULL',
                ];
                break;

            default:
                throw new \InvalidArgumentException();
        }

        $path_validator = \Validator::make($request->toArray(), $rules);
        if ($path_validator->fails()) {
            foreach ($path_validator->errors()->all() as $error) {
                $validator->errors()->add('path', $error);
            }
            return;
        }
    }
}
