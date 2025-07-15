<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

use Illuminate\Http\Request;
use App\Http\Requests\Api\Auth\StoreAdminRequest;
use App\Http\Requests\Api\Auth\StoreDoctorRequest;
use App\Http\Requests\Api\Auth\StorePatientRequest;

use App\Enums\UserType;
use App\Models\Patient;
use App\Models\Doctor;
use App\Models\Admin;
use App\Models\User;

use App\Http\Resources\DoctorResource;
use App\Http\Resources\AdminResource;
use App\Http\Resources\PatientResource;

class RegisterController extends Controller
{
    //
    public function handleImageUpload(Request $request)
    {
        if (!$request->hasFile('image')) {
            return null;
        }

        $image = $request->file('image');
        $imageName = Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move('uploads/users/', $imageName);

        return $imageName;
    }


    public function createUser(Request $request, UserType $type)
    {
        return User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => $type,
        ]);
    }


    public function storeAdmin(StoreAdminRequest $request)
    {
        $user = $this->createUser($request, UserType::ADMIN);

        $imageName = $this->handleImageUpload($request);
        $admin = Admin::create([
            'user_id' => $user->id,
            'image'   => $imageName ?? "admin.png",
        ]);

        $token = $user->createToken('Doctor-Booking-System')->plainTextToken;
        $data  = ['token' => $token];
        $data['user'] = new AdminResource($admin);

        return apiResponse($data, "Account created successfully", 201);
    }


    public function storeDoctor(StoreDoctorRequest $request)
    {
        $user = $this->createUser($request, UserType::DOCTOR);

        $imageName = $this->handleImageUpload($request);
        $doctor = Doctor::create([
            'user_id'           => $user->id,
            'specialization_id' => $request->specialization_id,
            'image'             => $imageName ?? "doctor.png",
            'bio'               => $request->bio,
            'phone'             => $request->phone,
        ]);

        $token = $user->createToken('Doctor-Booking-System')->plainTextToken;
        $data  = ['token' => $token];
        $data['user'] = new DoctorResource($doctor);

        return apiResponse($data, "Account created successfully", 201);
    }


    public function storePatient(StorePatientRequest $request)
    {
        $user = $this->createUser($request, UserType::PATIENT);

        $imageName = $this->handleImageUpload($request);
        $patient = Patient::create([
            'user_id'       => $user->id,
            'date_of_birth' => $request->date_of_birth ?? null,
            'gender'        => $request->gender ?? null,
            'image'         => $imageName ?? "patient.png",
            'phone'             => $request->phone,
        ]);

        $token = $user->createToken('Doctor-Booking-System')->plainTextToken;
        $data  = ['token' => $token];
        $data['user'] = new PatientResource($patient);

        return apiResponse($data, "Account created successfully", 201);
    }


}
