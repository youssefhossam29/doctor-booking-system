<?php

namespace App\Http\Requests\Api\Doctor;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

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
     * Inject doctor_id into the request before validation
     */
    protected function prepareForValidation(): void
    {
        $this->replace(array_merge($this->all(), [
            'doctor_id' => optional($this->user()->doctor)->id,
        ]));

    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        $doctorId = optional($this->user()->doctor)->id ?? 0;

        return [
            //
            'doctor_id'     => ['required', 'integer', 'exists:doctors,id'],
            'day_of_week' => [
                'required',
                Rule::in(['sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday']),
                Rule::unique('schedules', 'day_of_week')->where(fn ($q) => $q->where('doctor_id', $doctorId)),
            ],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'slot_duration' => ['required', 'integer', 'min:5', 'max:60'],
        ];
    }

    public function messages(): array
    {
        return [
            'day_of_week.unique' => 'You already have a schedule on this day.',
        ];
    }

}
