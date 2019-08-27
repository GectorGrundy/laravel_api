<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Request;

/**
 * Валидация realpath домена при доб./ред. лендинга/прелендинга
 */
class RealpathRule
{
    public function validate(Request $request, Validator $validator)
    {
        $realpath = $request->input('realpath');

        if (empty($realpath)) {
            return $validator->errors()->add('realpath', trans('domains.realpath.incorrect'));
        }

        $realpath = ends_with($realpath, '/') ? substr($realpath, 0, -1) : $realpath;
        $realpath = !starts_with($realpath, '/') ? '/' . $realpath : $realpath;
        $realpath = config('env.landings_path') . $realpath;

        if (!\File::exists($realpath . '/index.html')) {
            return $validator->errors()->add('realpath', trans('domains.realpath.incorrect'));
        }

        $request->merge(['realpath' => $realpath]);
    }
}