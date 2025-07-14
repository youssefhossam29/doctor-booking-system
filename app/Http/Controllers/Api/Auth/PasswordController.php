<?php

namespace App\Http\Controllers\Api\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Http\Requests\Api\Auth\PasswordRequest;
use Illuminate\Support\Facades\Hash;

class PasswordController extends Controller
{
    //
    public function update(PasswordRequest $request)
    {
        $request->user()->update([
            'password' => Hash::make($request['password']),
        ]);

        return apiResponse([], "Password updated successfully.", 200);
    }
}
