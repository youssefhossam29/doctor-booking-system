<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use App\Http\Resources\SpecializationResource;
use App\Models\Specialization;
use App\Http\Resources\DoctorResource;

class SpecializationController extends Controller
{

    public function index()
    {
        $specializations = Specialization::latest()->get();
        if ($specializations->isEmpty()) {
            return apiResponse([], "No specialization found.", 200);
        }

        $specializations = SpecializationResource::collection($specializations);
        return apiResponse($specializations, "Specialization fetched successfully.", 200);
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

        $specializations = Specialization::where('name', 'LIKE', "%{$search}%")
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($specializations->isEmpty()) {
            return apiResponse([], "No specializations matched your search.", 200);
        }

        $specializations = SpecializationResource::collection($specializations);
        return apiResponse($specializations, "Search results for: $search", 200);
    }


    public function doctors(Specialization $specialization)
    {
        $doctors = $specialization->doctors()
            ->with(['user', 'specialization'])
            ->latest()
            ->get();

        if ($doctors->isEmpty()) {
            return apiResponse([], "No doctors found for this specialization.", 200);
        }

        $doctors = DoctorResource::collection($doctors);
        return apiResponse($doctors, "Doctors fetched successfully.", 200);
    }

}
