<?php

namespace App\Http\Controllers\Api\Doctor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Http\Requests\Api\Auth\UpdateDoctorRequest;
use App\Http\Resources\DoctorResource;

class DoctorProfileController extends Controller
{
    //
    public function show(){
        $user = auth()->user();
        $doctor = $user->doctor;
        $doctor = new DoctorResource($doctor);
        return apiResponse($doctor, "Doctor Fetched successfully.", 200);
    }


    public function handleImageUpload($image)
    {
        $imageName = Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move('uploads/users/', $imageName);
        return $imageName;
    }


    public function updateUserData(array $data)
    {
        $user = auth()->user();
        $user->fill($data);

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();
        return $user;
    }


    public function update(UpdateDoctorProfileRequest $request)
    {
        $user = $this->updateUserData($request->validated());
        $doctor = $user->doctor;

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
        $doctor->save();

        $doctor = new DoctorResource($doctor);
        return apiResponse($doctor, "Profile updated successfully.", 200);
    }


    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'current_password']
        ]);

        if($validator->fails()){
            return apiResponse($validator->errors(), "validation error", 422);
        }

        $user = $request->user();
        $doctor = $user->doctor;

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
