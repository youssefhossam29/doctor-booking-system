<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class StoreAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'doctor_id' => [
                'required',
                'exists:doctors,id',
                Rule::unique('appointments')
                    ->where(fn($query) =>
                        $query->where('appointment_date', $this->appointment_date)
                              ->where('appointment_time', $this->appointment_time)
                    ),
            ],

            'patient_id' => [
                'required',
                'exists:patients,id',
                Rule::unique('appointments')
                    ->where(fn($query) =>
                        $query->where('appointment_date', $this->appointment_date)
                              ->where('appointment_time', $this->appointment_time)
                    ),
            ],

            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }


    public function messages(): array
    {
        return [
            'doctor_id.unique' => 'This doctor already has an appointment at the selected date and time.',
            'patient_id.unique' => 'This patient already has an appointment at the selected date and time.',
            'doctor_id.exists' => 'The selected doctor does not exist.',
            'patient_id.exists' => 'The selected patient does not exist.',
        ];
    }


    public function withValidator($validator)
    {
        $validator->after(function ($validator) {
            $doctorId = $this->doctor_id;
            $date = $this->appointment_date;
            $time = $this->appointment_time;

            $slotExists = DB::table('doctor_slots')
                ->where('doctor_id', $doctorId)
                ->where('date', $date)
                ->where('start_time', $time)
                ->where('is_available', 1)
                ->exists();

            if (! $slotExists) {
                $validator->errors()->add('appointment_time', 'The selected time is not available for this doctor.');
            }
        });
    }
}

