<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Requests\Api\Admin\StoreAppointmentRequest;
use App\Http\Requests\Api\Admin\UpdateAppointmentRequest;
use App\Http\Resources\AppointmentResource;
use App\Enums\UserType;

use App\Models\User;
use App\Models\Appointment;
use App\Models\Doctor;
use App\Models\Patient;
use App\Models\DoctorSlot;

class AppointmentController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(){
        $appointments = Appointment::with([
            'doctor.user',
            'doctor.specialization',
            'patient.user',
        ])->latest()->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No appointments found.", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "appointments fetched successfully.", 200);
    }


    public function search(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $search = $request->input('search');
        $appointments = Appointment::with([
                'doctor.user',
                'doctor.specialization',
                'patient.user',
            ])
            ->where(function ($query) use ($search) {
                $query->whereHas('patient.user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('doctor.user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
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
        $validator = Validator::make($request->all(), [
            'from_date' => 'required|date',
            'to_date'   => 'required|date|after_or_equal:from_date',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $from = $request->input('from_date');
        $to = $request->input('to_date');

        $appointments = Appointment::with([
                'doctor.user',
                'doctor.specialization',
                'patient.user',
            ])
            ->whereBetween('appointment_date', [$from, $to])
            ->orderBy('appointment_date', 'ASC')
            ->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No appointments found between $from and $to", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "Appointments fetched successfully between $from and $to", 200);
    }


    public function store(StoreAppointmentRequest $request)
    {
        $appointment = Appointment::create([
            'doctor_id' => $request->doctor_id,
            'patient_id' => $request->patient_id,
            'appointment_date' => $request->appointment_date,
            'appointment_time' => $request->appointment_time,
            'status' => $request->status,
            'notes' => $request->notes,
        ]);

        $this->markSlotAsUnavailable(
            $request->doctor_id,
            $request->appointment_date,
            $request->appointment_time
        );

        $appointment = new AppointmentResource($appointment);
        return apiResponse($appointment, "Appointment created successfully", 201);
    }


    public function update(UpdateAppointmentRequest $request, Appointment $appointment)
    {
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
        $appointment = new AppointmentResource($appointment);
        return apiResponse($appointment, "Appointment fetched successfully", 200);
    }


    public function destroy(Appointment $appointment)
    {
        $doctorId = $appointment->doctor_id;
        $appointmentDate = $appointment->appointment_date;
        $appointmentTime = $appointment->appointment_time;

        $appointment->delete();

        $this->markSlotAsAvailable($doctorId, $appointmentDate, $appointmentTime);

        return apiResponse([], "Appointment deleted successfully", 200);
    }


    public function indexByPatient(Patient $patient)
    {
        $appointments = $patient->appointments()
            ->with([
                'doctor.user',
                'doctor.specialization',
                'patient.user',
            ])
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No appointments found for {$patient->user->name}.", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "Appointments for {$patient->user->name} fetched successfully.", 200);
    }


    public function indexByDoctor(Doctor $doctor)
    {
        $appointments = $doctor->appointments()
            ->with([
                'doctor.user',
                'doctor.specialization',
                'patient.user',
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


    public function indexByDoctorAndPatient(Doctor $doctor, Patient $patient)
    {
        $appointments = Appointment::with([
                'doctor.user',
                'doctor.specialization',
                'patient.user',
            ])
            ->where('doctor_id', $doctor->id)
            ->where('patient_id', $patient->id)
            ->orderBy('appointment_date')
            ->orderBy('appointment_time')
            ->get();

        if ($appointments->isEmpty()) {
            return apiResponse([], "No appointments found for {$patient->user->name} with {$doctor->user->name}.", 200);
        }

        $appointments = AppointmentResource::collection($appointments);
        return apiResponse($appointments, "Appointments for {$doctor->user->name} with {$patient->user->name} fetched successfully.", 200);
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
