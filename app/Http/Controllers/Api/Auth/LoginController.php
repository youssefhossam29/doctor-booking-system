<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Auth\LoginRequest;
use App\Enums\UserType;

use App\Http\Resources\DoctorResource;
use App\Http\Resources\AdminResource;
use App\Http\Resources\PatientResource;

class LoginController extends Controller
{
    //
    public function store(LoginRequest $request)
    {
        $request->authenticate();

        $token = auth()->user()->createToken('Doctor-Booking-System')->plainTextToken;
        $data['token'] = $token;
        $user = auth()->user();
        $type = $user->type;

        if ($type === UserType::DOCTOR && $user->doctor) {
            $data['user'] = new DoctorResource($user->doctor);

        } elseif ($type === UserType::PATIENT && $user->patient) {
            $data['user'] = new PatientResource($user->patient);

        } elseif ($type === UserType::ADMIN  && $user->admin) {
            $data['user'] = new AdminResource($user->admin);

        } else {
            return apiResponse([], "User not found", 404);
        }

        return apiResponse($data, "User logged in successfully", 200);
    }


    public function destroy(Request $request)
    {
        $request->user()->tokens()->delete();
        return apiResponse([], "User logged out successfully", 200);
    }
}
