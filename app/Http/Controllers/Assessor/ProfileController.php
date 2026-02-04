<?php

namespace App\Http\Controllers\Assessor;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class ProfileController extends Controller
{
    public function edit()
    {
        $user = auth()->user();
        $assessorProfile = $user->assessorProfile;

        return view('assessor.profile.edit', compact('user', 'assessorProfile'));
    }

    public function update(Request $request)
    {
        $user = auth()->user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],

            'title' => ['nullable', 'string', 'max:255'],
        ]);

        $user->update([
            'name' => $validated['name'],

        ]);

        if ($user->assessorProfile && isset($validated['title'])) {
            $user->assessorProfile->update([
                'title' => $validated['title'],
            ]);
        }

        return redirect()->route('assessor.profile.edit')
            ->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'current_password'],
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        auth()->user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        return redirect()->route('assessor.profile.edit')
            ->with('success', 'Password berhasil diperbarui.');
    }
}
