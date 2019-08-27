<?php
declare(strict_types=1);

namespace App\Http\Requests\Auth;

use App\Models\User;
use App\Http\Requests\Request;
use GuzzleHttp\Client;
use Illuminate\Contracts\Validation\Validator;

class RegistrationRequest extends Request
{
    public function rules()
    {
        return [
            'user_role' => 'required|in:' . User::ADVERTISER . ',' . User::PUBLISHER,
            'email' => 'required|email|unique:users,email',
            'password' => 'required_if:user_role,' . User::PUBLISHER . '|string|max:255|min:8',
            'g-recaptcha-response' => 'required_if:user_role,' . User::PUBLISHER . '|string',
            'phone' => 'required_if:user_role,' . User::ADVERTISER,
            'contacts' => 'required_if:user_role,' . User::ADVERTISER . '|string',
            'geo' => 'required_if:user_role,' . User::ADVERTISER . '|string',
        ];
    }

    public function messages(): array
    {
        return [
            'email.unique' => trans('publishers.email.unique'),
            'password.min' => trans('users.password.min'),
            'g-recaptcha-response.required_if' => trans('auth.on_recaptcha_error'),
        ];
    }

    protected function getFailedValidationMessage()
    {
        return trans('api.on_error');
    }

    public function moreValidation(Validator $validator)
    {
        $validator->after(function ($validator) {
            if ($this->input('user_role') === User::PUBLISHER && !$this->isValidRecaptcha()) {
                $validator->errors()->add('g-recaptcha-response', trans('auth.on_recaptcha_error'));
            }
        });
    }

    public function isValidRecaptcha()
    {
        $params = ['form_params' => [
            'secret' => config('env.google_recaptcha_secret'),
            'response' => $this->input('g-recaptcha-response'),
            'remoteip' => $this->ip(),
        ]];

        /**
         * @var Client $http
         */
        $http = app(Client::class);

        $response = (string)$http->post('https://www.google.com/recaptcha/api/siteverify', $params)->getBody();

        return json_decode($response, true)['success'];
    }
}
