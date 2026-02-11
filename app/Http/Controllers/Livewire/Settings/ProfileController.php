<?php

namespace App\Http\Controllers\Livewire\Settings;

use App\Models\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ProfileController extends Controller
{
    //
    public function index()
    {
        //
        return view('livewire.settings.profile');
    }
    
    public function update(Request $request,User $user)
    {
        //
        $data = $request->validate([
            'first_name' => ['required', 'string', 'max:255'],
            'last_name'  => ['required', 'string', 'max:255'],
            'nickname'   => ['nullable', 'string', 'max:255'],
        ]);

        $user->update($data);

        return redirect()
            ->back()
            ->with('success', 'User updated successfully.');
    }
}
