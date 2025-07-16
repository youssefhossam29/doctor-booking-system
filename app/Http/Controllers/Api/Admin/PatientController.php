<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\Api\Admin\StorePatientRequest;
use App\Http\Requests\Api\Admin\UpdatePatientRequest;
use App\Http\Resources\PatientResource;
use App\Enums\UserType;
use App\Models\User;
use App\Models\Patient;

class PatientController extends Controller
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
        $patients = Patient::latest()->get();
        if ($patients->isEmpty()) {
            return apiResponse([], "No patients found.", 200);
        }

        $patients = PatientResource::collection($patients);
        return apiResponse($patients, "Patients fetched successfully.", 200);
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
        $patients = Patient::whereHas('user', function ($query) use ($search) {
                $query->where('name', 'LIKE', "%{$search}%")
                    ->orWhere('email', 'LIKE', "%{$search}%");
            })
            ->with('user')
            ->orderBy('created_at', 'DESC')
            ->get();

        if ($patients->isEmpty()) {
            return apiResponse([], "No results found for: $search", 200);
        }

        $patients = PatientResource::collection($patients);
        return apiResponse($patients, "Search results for: $search", 200);
    }


    public function show(Patient $patient){
        $patient = new PatientResource($patient);
        return apiResponse($patient, "Patient Fetched successfully.", 200);
    }


    public function store(StorePatientRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => UserType::PATIENT,
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $this->handleImageUpload($image);
        }

        $patient = Patient::create([
            'user_id'       => $user->id,
            'date_of_birth' => $request->date_of_birth ?? null,
            'gender'        => $request->gender ?? null,
            'image'         => $imageName ?? "patient.png",
            'phone'             => $request->phone,
        ]);

        $patient = new PatientResource($patient);

        return apiResponse($patient, "Account created successfully", 201);
    }


    public function update(UpdatePatientRequest $request, Patient $patient)
    {
        $user = $patient->user;
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

        $patient->phone = $request->input('phone', $patient->phone);
        $patient->date_of_birth = $request->input('date_of_birth', $patient->date_of_birth);
        $patient->gender = $request->input('gender', $patient->gender);
        $patient->save();

        $patient = new PatientResource($patient);
        return apiResponse($patient, "Profile updated successfully.", 200);
    }


    public function destroy(Patient $patient){
        $user = $patient->user;

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
