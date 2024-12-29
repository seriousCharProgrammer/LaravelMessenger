<?php
namespace App\Http\Controllers;

use App\Models\User;
use App\Traits\FileUploadTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class UserProfileController extends Controller
{
    use FileUploadTrait;

    public function update(Request $request)
    {
        $request->validate([
            'avatar' => ['nullable', 'image', 'max:500'],
            'name' => ['required', 'string', 'max:50'],
            'user_id' => ['required', 'string', 'max:255', 'unique:users,username,' . Auth::id()],
            'email' => ['required', 'email', 'max:100']
        ]);

        $user = User::find(Auth::id());

        if (!$user) {
            return response(['message' => 'User not found'], 404);
        }

        $avatarPath = $this->uploadFile($request, 'avatar');

        if ($avatarPath) {
            $user->avatar = $avatarPath;
        }

        $user->name = $request->name;
        $user->username = $request->user_id;
        $user->email = $request->email;

        if ($request->filled('current_password')) {
            $request->validate([
                'current_password' => ['required', 'current_password'],
                'password' => ['required', 'string', 'min:8', 'confirmed']
            ]);

            $user->password = Hash::make($request->password);
        }

        $user->save();

        if (function_exists('notyf')) {
            notyf()->addSuccess('Updated Successfully.');
        }

        return response(['message' => 'Updated Successfully!'], 200);
    }
}
