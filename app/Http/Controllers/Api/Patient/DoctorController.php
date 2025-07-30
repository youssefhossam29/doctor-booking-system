<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\DoctorResource;
use App\Models\User;
use App\Models\Doctor;

class DoctorController extends Controller
{
    public function index()
    {
        $doctors = Doctor::with('specialization', 'user')->get();
        if ($doctors->isEmpty()) {
            return apiResponse([], "No doctors found.", 200);
        }

        $doctors = DoctorResource::collection($doctors);
        return apiResponse($doctors, "Doctors fetched successfully.", 200);
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
        $doctors = Doctor::with(['user', 'specialization'])
            ->where(function ($query) use ($search) {
                $query->whereHas('user', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                })
                ->orWhereHas('specialization', function ($q) use ($search) {
                    $q->where('name', 'LIKE', "%{$search}%");
                });
            })
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($doctors->isEmpty()) {
            return apiResponse([], "No results found for: $search", 200);
        }

        $doctors = DoctorResource::collection($doctors);
        return apiResponse($doctors, "Search results for: $search", 200);
    }


    public function show(Doctor $doctor){
        $doctor = new DoctorResource($doctor);

        return apiResponse([
            'doctor' => $doctor,
            'schedule' => $doctor->schedules
            ],
            "Doctor fetched successfully.", 200);
    }

}
