<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserEditRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, mixed>
     */
    public function rules()
    {
        return [
            'firstname' => ['required', 'max:60'],
            'lastname' => ['required', 'max:60'],
            'password' => ['required', 'max:60'],
            'birthdate' => ['required', 'date'],
            'type' => ['required'],
            'country_name' => ['required'],
            'phone' => ['required', 'max:20'],
            'email' => ['required', 'email', 'max:60'],
            'previousEmail' => ['required', 'email', 'max:60'],
        ];
    }
}
