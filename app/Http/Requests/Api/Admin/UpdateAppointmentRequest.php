<?php

namespace App\Http\Requests\Api\Admin;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Contracts\Validation\Validator;

use App\Models\Appointment;
use App\Models\DoctorSlot;
use Carbon\Carbon;

class UpdateAppointmentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            'appointment_date' => ['required', 'date', 'after_or_equal:today'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'status' => ['required', Rule::in(['pending', 'completed', 'cancelled'])],
            'notes' => ['nullable', 'string', 'max:255'],
        ];
    }


    public function withValidator(Validator $validator)
    {
        $validator->after(function ($validator) {
            $appointment = $this->route('appointment');

            if (!$appointment) {
                return;
            }

            $doctorId = $appointment->doctor_id;
            $patientId = $appointment->patient_id;
            $date = $this->input('appointment_date');
            $time = $this->input('appointment_time');


            // 1. Check if the doctor has a valid available slot
            $slot = DoctorSlot::where('doctor_id', $doctorId)
                ->where('date', $date)
                ->where('start_time', $time)
                ->first();

            $currentDate = $appointment->appointment_date;
            $currentTime = Carbon::createFromFormat('H:i:s', $appointment->appointment_time)->format('H:i');

            $isSameDate = $currentDate === $date;
            $isSameTime = $currentTime === $time;

            if (!$slot) {
                $validator->errors()->add('appointment_time', 'This time is not a valid slot for this doctor.');
            } elseif (!$slot->is_available && !($isSameDate && $isSameTime)) {
                // Slot is exists but taken by another patient
                $validator->errors()->add('appointment_time', "This doctor isn't available at this time.");
            }

            // 2. Ensure no conflicting appointment exists for the doctor
            $doctorConflict = Appointment::where('doctor_id', $doctorId)
                ->where('appointment_date', $date)
                ->where('appointment_time', $time)
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($doctorConflict) {
                $validator->errors()->add('appointment_time', 'This doctor already has an appointment at the selected date and time.');
            }

            // 3. Ensure no conflicting appointment exists for the patient
            $patientConflict = Appointment::where('patient_id', $patientId)
                ->where('appointment_date', $date)
                ->where('appointment_time', $time)
                ->where('id', '!=', $appointment->id)
                ->exists();

            if ($patientConflict) {
                $validator->errors()->add('appointment_time', 'This patient already has an appointment at the selected date and time.');
            }
        });
    }
}
