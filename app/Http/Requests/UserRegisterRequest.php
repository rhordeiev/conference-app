<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class UserRegisterRequest extends FormRequest
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
    public function rules(): array
    {
        return [
            'firstname' => ['required', 'max:60'],
            'lastname' => ['required', 'max:60'],
            'password' => ['required', 'max:60'],
            'birthdate' => ['required', 'date'],
            'type' => ['required'],
            'country_name' => ['required'],
            'phone' => ['required', 'max:20'],
            'email' => ['required', 'email', 'unique:users', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'firstname.required' => 'First name is required.',
            'lastname.required' => 'Last name is required.',
            'birthdate.required' => 'Birth date is required.',
            'phone.required' => 'Phone is required.',
            'email.required' => 'Email is required.',
            'password.required' => 'Password is required.',
        ];
    }
}
