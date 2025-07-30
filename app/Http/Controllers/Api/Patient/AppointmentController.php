<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\Api\Patient\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\DoctorSlot;

class AppointmentController extends Controller
{

    public function index()
    {
        $this->authorize('viewAny', Appointment::class);

        $patient = request()->user()->patient;

        $appointments = Appointment::with([
            'patient.user',
            'doctor.specialization',
            'doctor.user',
        ])->where('patient_id', $patient->id)->latest()->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No appointments found.", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "appointments fetched successfully.", 200);
    }


    public function search(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $search = $request->input('search');
        $patient = request()->user()->patient;

        $appointments = Appointment::with([
                'patient.user',
                'doctor.specialization',
                'doctor.user',
            ])
            ->where('patient_id', $patient->id)
            ->whereHas('doctor.user', function ($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No results found for: $search", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "Search results for: $search", 200);
    }


    public function appointmentsBetweenDates(Request $request)
    {
        $this->authorize('viewAny', Appointment::class);

        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');
        $patient = request()->user()->patient;

        $appointments = Appointment::with([
                'patient.user',
                'doctor.specialization',
                'doctor.user',
            ])
            ->where('patient_id', $patient->id)
            ->whereBetween('appointment_date', [$from, $to])
            ->orderBy('appointment_date', 'ASC')
            ->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No appointments found between $from and $to", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "Appointments fetched successfully between $from and $to", 200);
    }


    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
        $this->authorize('update', $appointment);

        $oldDate = $appointment->appointment_date;
        $oldTime = $appointment->appointment_time;
        $newDate = $request->appointment_date;
        $newTime = $request->appointment_time;

        if ($oldDate !== $newDate || $oldTime !== $newTime) {
            $this->markSlotAsAvailable($appointment->doctor_id, $oldDate, $oldTime);
        }

        $appointment->appointment_date = $newDate;
        $appointment->appointment_time = $newTime;
        $appointment->status = $request->input('status', $appointment->status);
        $appointment->notes = $request->input('notes', $appointment->notes);
        $appointment->save();

        $this->markSlotAsUnavailable($appointment->doctor_id, $newDate, $newTime);

        return apiResponse(new AppointmentResource($appointment), "Appointment updated successfully", 200);
    }


    public function show(Appointment $appointment)
    {
        $this->authorize('view', $appointment);

        $appointment = new AppointmentResource($appointment);
        return apiResponse($appointment, "Appointment fetched successfully", 200);
    }


    public function cancel(Appointment $appointment)
    {
        $this->authorize('cancel', $appointment);

        $doctorId = $appointment->doctor_id;
        $appointmentDate = $appointment->appointment_date;
        $appointmentTime = $appointment->appointment_time;

        $appointment->status = 'cancelled';
        $appointment->save();

        $this->markSlotAsAvailable($doctorId, $appointmentDate, $appointmentTime);

        return apiResponse([], "Appointment cancelled successfully", 200);
    }


    public function indexByDoctor(Doctor $doctor)
    {
        $patient = request()->user()->patient;

        $appointments = $doctor->appointments()
            ->where('patient_id', $patient->id)
            ->with([
                'patient.user',
                'doctor.specialization',
                'doctor.user',
            ])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No appointments found for {$doctor->user->name}.", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "Appointments for {$doctor->user->name} fetched successfully.", 200);
    }


    protected function markSlotAsUnavailable($doctorId, $date, $startTime)
    {
        DoctorSlot::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('start_time', $startTime)
            ->update(['is_available' => 0]);
    }


    protected function markSlotAsAvailable($doctorId, $date, $startTime)
    {
        DoctorSlot::where('doctor_id', $doctorId)
            ->where('date', $date)
            ->where('start_time', $startTime)
            ->update(['is_available' => 1]);
    }

}
