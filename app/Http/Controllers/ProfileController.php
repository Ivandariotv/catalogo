<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class ProfileController extends Controller
{
    /**
     * Display the specified resource.
     *
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function show()
    {
        $userId = Auth::Id();
        $user = User::addUrlImage()->find($userId);

        return $user;
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Models\Client  $client
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $userId = Auth::Id();
        $user = User::addUrlImage()->find($userId);

        $request->validate([
            "name" => 'required|string|max:255',
            "phone" => [
                'required',
                'string',
                Rule::unique('001_droi_p2_t1_clients')->ignore($user->id)->where(
                    function ($query) use ($user) {
                        $query->where('Phone', '<>', $user->Phone);
                    }
                ),
                'regex:/^\d{3}\d{3}\d{4}$/'
            ]
        ]);


        $user->Name = $request->name;
        if ($user->Phone != $request->phone) $user->Phone = $request->phone;

        $user->update();

        return response()->json([], 204);
    }


    public function updatePin(Request $request)
    {
        $userId = Auth::Id();
        $user = User::find($userId);

        $request->validate([
            "current_pin" => 'required|string|min:6|max:6|password',
            "new_pin" => 'required|string|min:6|max:6|confirmed'
        ]);

        if (!(Hash::check($request->current_pin, $user->password))) return response()->json(
            ["message" => 'Incorrect current password provided'],
            401
        );

        // Hashear una contraseña
        $user->password = Hash::make($request->new_pin);
        $user->save();

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            "accessToken" => $token,
            "expiresIn" => -1,
        ]);
    }


    public function updateImage(Request $request)
    {
        $request->validate([
            "image_data" => 'required|string'
        ]);

        $userId = Auth::Id();
        $user = User::find($userId);

        $existImage = Storage::disk('ftp')->exists($user->photograph);
        if ($existImage) Storage::disk('ftp')->delete($user->photograph);

        $image_data = base64_decode($request->image_data);
        $image_name = hash('sha256', microtime(true) . $userId) . ".jpg";

        $user->photograph = $image_name;
        $user->update();

        Storage::disk('ftp')->put($image_name, $image_data);
        return response()->json([], 204);
    }
}
