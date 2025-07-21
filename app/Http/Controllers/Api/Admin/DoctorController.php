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
use App\Http\Requests\Api\Admin\UpdateDoctorRequest;
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


    public function index()
    {
        $doctors = Doctor::latest()->get();
        if ($doctors->isEmpty()) {
            return apiResponse([], "No doctors found.", 200);
        }

        $doctors = DoctorResource::collection($doctors);
        return apiResponse($doctors, "doctors fetched successfully.", 200);
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
        $doctors = Doctor::where(function ($query) use ($search) {
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
        return apiResponse($doctor, "Doctor Fetched successfully.", 200);
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


    public function update(UpdateDoctorRequest $request, Doctor $doctor)
    {
        $user = $doctor->user;
        $user->fill($request->validated());

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $old_image = $doctor->image;
            $old_image_path = 'uploads/users/' . $doctor->image;

            $newImage = $this->handleImageUpload($image);
            $doctor->image = $newImage;

            if (File::exists($old_image_path) && $old_image != "doctor.png") {
                File::delete($old_image_path);
            }
        }

        $doctor->phone = $request->input('phone', $doctor->phone);
        $doctor->bio = $request->input('bio', $doctor->bio);
        $doctor->specialization_id = $request->input('specialization_id', $doctor->specialization_id);
        $doctor->save();

        $doctor = new DoctorResource($doctor);
        return apiResponse($doctor, "Profile updated successfully.", 200);
    }


    public function destroy(Doctor $doctor){
        $user = $doctor->user;

        if ($doctor && $doctor->image !== 'doctor.png') {
            $imagePath = 'uploads/users/' . $doctor->image;
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $user->tokens()->delete();
        $user->delete();
        return apiResponse([], "Account deleted Successfully", 200);
    }

}

