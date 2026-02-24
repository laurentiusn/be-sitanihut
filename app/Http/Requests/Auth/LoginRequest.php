<?php

namespace App\Http\Requests\Auth;

use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'login'         => 'required|string',
            'password'      => 'required|string',
            'platform'      => 'nullable|string',
            'device_name'   => 'nullable|string|max:255',
            'device_unq'    => 'nullable|string|max:255',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        throw new HttpResponseException(response()->json([
            'message' => 'Data tidak sesuai',
            'statusCode' => 401,
            'data' => []
        ], 401));
    }
}