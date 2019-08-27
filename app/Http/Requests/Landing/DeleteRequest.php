<?php
declare(strict_types=1);

namespace App\Http\Requests\Landing;

use App\Http\Requests\Request;
use Hashids;

class DeleteRequest extends Request
{
    public function authorize()
    {
        $landing_decoded_data = Hashids::decode($this->input('hash'));
        if (is_null($landing_decoded_data)) {
            return false;
        }

        $landing_id = $landing_decoded_data[0];
        $this->merge(['landing_id' => $landing_id]);

        return true;
    }

    public function rules()
    {
        return [
            'hash' => 'required|exists:landings,hash,deleted_at,NULL'
        ];
    }

    public function messages()
    {
        return [];
    }

    protected function getFailedValidationMessage()
    {
        return trans('landings.on_delete_error');
    }
}
