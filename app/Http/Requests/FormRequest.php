<?php
declare(strict_types=1);

namespace App\Http\Requests;

use Illuminate\Contracts\Validation\Validator;
use Symfony\Component\HttpKernel\Exception\UnauthorizedHttpException;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Dingo\Api\Exception\ResourceException;
use Illuminate\Foundation\Http\FormRequest as IlluminateFormRequest;

class FormRequest extends IlluminateFormRequest
{
    /**
     * Handle a failed validation attempt.
     *
     * @param Validator $validator
     */
    protected function failedValidation(Validator $validator)
    {
        throw new ResourceException($this->getFailedValidationMessage(), $validator->getMessageBag());
    }

    /**
     * Handle a failed authorization attempt.
     *
     */
    protected function failedAuthorization()
    {
        if (strpos($this->url(), 'api.')) {
            throw new AccessDeniedHttpException($this->getFailedAuthorizationMessage());
        }

        parent::failedAuthorization();
    }

    /**
     * Get message on validation error
     *
     */
    protected function getFailedValidationMessage()
    {
        return trans('messages.on_validation_error');
    }

    /**
     * Get message on authorization error
     *
     */
    protected function getFailedAuthorizationMessage()
    {
        return trans('messages.unauthorized');
    }
}
