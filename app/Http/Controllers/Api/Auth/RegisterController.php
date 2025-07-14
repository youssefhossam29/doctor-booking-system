<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Auth\RegisterRequest;
use Illuminate\Support\Facades\Hash;

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
    public function store(RegisterRequest $request)
    {
        $type = UserType::from($request->type);

        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => $request->type,
        ]);

        $token = $user->createToken('Doctor-Booking-System')->plainTextToken;
        $data  = ['token' => $token];

        if ($type === UserType::DOCTOR) {
            $doctor = Doctor::create([
                'user_id'           => $user->id,
                'specialization_id' => $request->specialization_id,
                'image'             => $request->image ?? "avatar.png",
            ]);

            $data['user'] = new DoctorResource($doctor);

        } elseif ($type === UserType::PATIENT) {
            $paient = Patient::create([
                'user_id'       => $user->id,
                'date_of_birth' => $request->date_of_birth ?? null,
                'gender'        => $request->gender ?? null,
                'image'         => $request->image ?? "avatar.png",
            ]);

            $data['user'] = new PatientResource($patient);

        } elseif ($type === UserType::ADMIN) {
            $admin = Admin::create([
                'user_id' => $user->id,
                'image'   => $request->image ?? "avatar.png",
            ]);

            $data['user'] = new AdminResource($admin);
        }

        return apiResponse($data, "Account created successfully", 201);
    }

}
