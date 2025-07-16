<?php

namespace App\Http\Controllers\Api\Patient;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;

use App\Http\Requests\Api\Patient\UpdatePatientRequest;
use App\Http\Resources\PatientResource;

class PatientController extends Controller
{
    //
    public function show(){
        $user = auth()->user();
        $patient = $user->patient;
        $patient = new PatientResource($patient);
        return apiResponse($patient, "Patient Fetched successfully.", 200);
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


    public function update(UpdatePatientRequest $request)
    {
        $user = $this->updateUserData($request->validated());
        $patient = $user->patient;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $old_image = $patient->image;
            $old_image_path = 'uploads/users/' . $patient->image;

            $newImage = $this->handleImageUpload($image);
            $patient->image = $newImage;

            if (File::exists($old_image_path) && $old_image != "patient.png") {
                File::delete($old_image_path);
            }
        }

        $patient->phone = $request->input('phone', $patient->phone);
        $patient->date_of_birth = $request->input('date_of_birth', $patient->date_of_birth);
        $patient->gender = $request->input('gender', $patient->gender);
        $patient->save();

        $patient = new PatientResource($patient);
        return apiResponse($patient, "Profile updated successfully.", 200);
    }


    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'current_password']
        ]);

        if($validator->fails()){
            return apiResponse($validator->errors(), "validation error", 422);
        }

        $user = $request->user();
        $patient = $user->patient;

        if ($patient && $patient->image !== 'patient.png') {
            $imagePath = 'uploads/users/' . $patient->image;
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $user->tokens()->delete();
        $user->delete();
        return apiResponse([], "Account deleted Successfully", 200);
    }

}
