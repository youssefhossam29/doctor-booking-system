<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\Api\Admin\StoreDoctorRequest;
use App\Http\Resources\DoctorResource;
use App\Enums\UserType;
use App\Models\User;
use App\Models\Doctor;

class DoctorController extends Controller
{
    //
    public function handleImageUpload($image)
    {
        $imageName = Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move('uploads/users/', $imageName);
        return $imageName;
    }


    public function store(StoreDoctorRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => UserType::DOCTOR,
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $this->handleImageUpload($image);
        }

        $doctor = Doctor::create([
            'user_id'           => $user->id,
            'specialization_id' => $request->specialization_id,
            'image'             => $imageName ?? "doctor.png",
            'bio'               => $request->bio,
            'phone'             => $request->phone,
        ]);

        $doctor = new DoctorResource($doctor);

        return apiResponse($doctor, "Account created successfully", 201);
    }

}
