<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use App\Http\Resources\PatientResource;
use App\Enums\UserType;
use App\Models\User;
use App\Models\Patient;
use App\Models\Appointment;

use Illuminate\Support\Facades\DB;

class PatientController extends Controller
{
    //
    public function index()
    {
        $doctor = request()->user()->doctor;

        $patientIds = Appointment::where('doctor_id', $doctor->id)->pluck('patient_id');
        $patients = Patient::with('user')->whereIn('id', $patientIds)->get();

        if ($patients->isEmpty()) {
            return apiResponse([], "No patients found.", 200);
        }

        $patients = PatientResource::collection($patients);
        return apiResponse($patients, "Patients fetched successfully.", 200);
    }


    public function search(Request $request)
    {
        //
        $validator = Validator::make($request->all(), [
            'search' => 'required|string|max:255',
        ]);

        if ($validator->fails()) {
            return apiResponse("Validation error", $validator->errors(), 422);
        }

        $search = $request->input('search');

        $doctor = request()->user()->doctor;
        $patientIds = Appointment::where('doctor_id', $doctor->id)->pluck('patient_id');
        $patients = Patient::with('user')->whereIn('id', $patientIds)
            ->whereHas('user', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($patients->isEmpty()) {
            return apiResponse([], "No results found for: $search", 200);
        }

        $patients = PatientResource::collection($patients);
        return apiResponse($patients, "Search results for: $search", 200);
    }


    public function show(Patient $patient)
    {
        $this->authorize('view', $patient);

        $patient = new PatientResource($patient);
        return apiResponse($patient, "Patient Fetched successfully.", 200);
    }
}
