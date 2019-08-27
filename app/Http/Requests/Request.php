<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Validator;

abstract class Request extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [];
    }

    public function validator(): \Illuminate\Validation\Validator
    {
        $v = Validator::make($this->input(), $this->rules(), $this->messages(), $this->attributes());

        if (!$v->fails() && method_exists($this, 'moreValidation')) {
            $this->moreValidation($v);
        }

        return $v;
    }
}
