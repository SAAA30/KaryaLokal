<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ProfileController extends Controller
{
    public function edit(Request $request): View
    {
        $user = $request->user()->load('artisanProfile');

        return view('profile.edit', compact('user'));
    }

    public function update(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = $request->user();
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
            'profile_photo' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:5120'],
            'current_password' => ['required_with:password', 'current_password'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
            'bio' => ['nullable', 'string', 'max:2000'],
            'maker_story' => ['nullable', 'string', 'max:5000'],
            'city' => ['nullable', 'string', 'max:100'],
            'province' => ['nullable', 'string', 'max:100'],
            'specialty' => ['nullable', 'string', 'max:150'],
            'started_year' => ['nullable', 'integer', 'min:1900', 'max:' . now()->year],
        ]);

        $photo = $data['profile_photo'] ?? null;
        $user->update(Arr::only($data, ['name', 'email', 'phone']));
        if ($photo) {
            if ($user->profile_photo_path) {
                Storage::disk('public')->delete($user->profile_photo_path);
            }
            $user->update(['profile_photo_path' => $photo->store('profile-photos', 'public')]);
        }
        if (filled($data['password'] ?? null)) {
            $user->update(['password' => $data['password']]);
        }

        if ($user->isArtisan()) {
            $user->artisanProfile()->updateOrCreate(
                ['user_id' => $user->id],
                Arr::only($data, ['bio', 'maker_story', 'city', 'province', 'specialty', 'started_year'])
            );
        }

        return redirect()->route('profile.edit')->with('success', 'Profil berhasil diperbarui.');
    }
}