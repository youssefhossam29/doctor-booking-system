<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreScheduleRequest extends FormRequest
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
            'doctor_id' => ['required', 'exists:doctors,id'],
            'day_of_week' => ['required', Rule::in(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday'])],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', 'integer', 'min:5', 'max:60'],
        ];
    }

    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $doctorId = $this->input('doctor_id');
            $dayOfWeek = $this->input('day_of_week');

            $exists = \App\Models\Schedule::where('doctor_id', $doctorId)
                ->where('day_of_week', $dayOfWeek)
                ->exists();

            if ($exists) {
                $validator->errors()->add('day_of_week', 'This doctor already has a schedule on this day.');
            }
        });
    }
}
