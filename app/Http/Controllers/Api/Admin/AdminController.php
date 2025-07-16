<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;

use App\Http\Requests\Api\Auth\UpdateAdminRequest;
use App\Http\Requests\Api\Auth\StoreAdminRequest;
use App\Http\Resources\AdminResource;
use App\Enums\UserType;
use App\Models\User;
use App\Models\Admin;

class AdminController extends Controller
{
    //
    public function show(){
        $user = auth()->user();
        $admin = $user->admin;
        $admin = new AdminResource($admin);
        return apiResponse($admin, "Admin Fetched successfully.", 200);
    }


    public function handleImageUpload($image)
    {
        $imageName = Str::random(10) . '_' . time() . '.' . $image->getClientOriginalExtension();
        $image->move('uploads/users/', $imageName);
        return $imageName;
    }


    public function store(StoreAdminRequest $request)
    {
        $user = User::create([
            'name'     => $request->name,
            'email'    => $request->email,
            'password' => Hash::make($request->password),
            'type'     => UserType::ADMIN,
        ]);

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $imageName = $this->handleImageUpload($image);
        }

        $admin = Admin::create([
            'user_id' => $user->id,
            'image'   => $imageName ?? "admin.png",
        ]);

        $admin = new AdminResource($admin);

        return apiResponse($admin, "Account created successfully", 201);
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


    public function update(UpdateAdminRequest $request)
    {
        $user = $this->updateUserData($request->validated());
        $admin = $user->admin;

        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $old_image = $admin->image;
            $old_image_path = 'uploads/users/' . $admin->image;

            $newImage = $this->handleImageUpload($image);
            $admin->image = $newImage;

            if (File::exists($old_image_path) && $old_image != "admin.png") {
                File::delete($old_image_path);
            }
            $admin->save();
        }

        $admin = new AdminResource($admin);
        return apiResponse($admin, "Profile updated successfully.", 200);
    }



    public function destroy(Request $request){
        $validator = Validator::make($request->all(), [
            'password' => ['required', 'current_password']
        ]);

        if($validator->fails()){
            return apiResponse($validator->errors(), "validation error", 422);
        }

        $user = $request->user();
        $admin = $user->admin;

        if ($admin && $admin->image !== 'admin.png') {
            $imagePath = 'uploads/users/' . $admin->image;
            if (File::exists($imagePath)) {
                File::delete($imagePath);
            }
        }

        $user->tokens()->delete();
        $user->delete();
        return apiResponse([], "Account deleted Successfully", 200);
    }

}
