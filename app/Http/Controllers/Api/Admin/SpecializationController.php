<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Http\Requests\Api\Admin\StoreSpecializationRequest;
use App\Http\Requests\Api\Admin\UpdateSpecializationRequest;
use App\Http\Resources\SpecializationResource;
use App\Models\Specialization;
use App\Http\Resources\DoctorResource;

class SpecializationController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $specialization = Specialization::latest()->get();
        if ($specialization->isEmpty()) {
            return apiResponse([], "No specialization found.", 200);
        }

        $specialization = SpecializationResource::collection($specialization);
        return apiResponse($specialization, "Specialization fetched successfully.", 200);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSpecializationRequest $request)
    {
        //
        $specialization = Specialization::create([
            'name' => $request->name,
        ]);

        $specialization = new SpecializationResource($specialization);
        return apiResponse($specialization, "Specialization created successfully", 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Specialization $specialization){
        $specialization = new SpecializationResource($specialization);
        return apiResponse($specialization, "Specialization Fetched successfully.", 200);
    }


    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateSpecializationRequest $request, Specialization $specialization)
    {
        //
        $specialization->name = $request->input('name', $specialization->name);
        $specialization->save();
        $specialization = new SpecializationResource($specialization);
        return apiResponse($specialization, "Specialization updated Successfully", 200);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Specialization $specialization)
    {
        //
        $specialization->delete();
        return apiResponse([], "Specialization deleted Successfully", 200);
    }


    /**
     * Display a listing of doctors under the given specialization.
     *
     * @param  \App\Models\Specialization  $specialization
     * @return \Illuminate\Http\JsonResponse
     */
    public function doctors(Specialization $specialization){
        $doctors = $specialization->doctors()->latest()->get();

        if ($doctors->isEmpty()) {
            return apiResponse([], "No doctors found for this specialization.", 200);
        }

        $doctors = DoctorResource::collection($doctors);
        return apiResponse($doctors, "doctors fetched successfully.", 200);
    }

}
