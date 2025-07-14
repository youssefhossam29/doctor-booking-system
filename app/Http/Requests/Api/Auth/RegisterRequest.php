<?php

namespace App\Http\Requests\Api\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Rule;

use App\Models\User;
use App\Enums\UserType;

class RegisterRequest extends FormRequest
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
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Password::defaults()],
            'type' => ['required', Rule::in(UserType::cases())],
            'specialization_id' => [
                'required_if:type,' . UserType::DOCTOR->value,
                'exists:specializations,id',
            ],
            'image' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp|max:2048'],
            'gender' => ['nullable', 'in:0,1'],
            'date_of_birth' => ['nullable', 'date', 'before:today'],
        ];
    }
}
