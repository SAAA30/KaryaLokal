<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\View\View;

class AdminUserController extends Controller
{
    public function index(Request $request): View
    {
        $roleCounts = User::query()
            ->selectRaw('role, COUNT(*) as aggregate')
            ->groupBy('role')
            ->pluck('aggregate', 'role');
        $totalAccounts = User::count();
        $users = User::query()
            ->when($request->filled('q'), function ($query) use ($request) {
                $term = trim($request->string('q')->toString());
                $query->where(fn ($users) => $users->where('name', 'like', "%{$term}%")
                    ->orWhere('email', 'like', "%{$term}%"));
            })
            ->when($request->filled('role') && isset(User::$roleLabel[$request->string('role')->toString()]),
                fn ($query) => $query->where('role', $request->string('role')->toString()))
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.users.index', [
            'users' => $users,
            'roles' => User::$roleLabel,
            'roleCounts' => $roleCounts,
            'totalAccounts' => $totalAccounts,
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone' => ['nullable', 'string', 'max:30'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'in:' . implode(',', array_keys(User::$roleLabel))],
        ]);
        $data['password'] = Hash::make($data['password']);
        $data['status_akun'] = 'aktif';
        User::create($data);

        return back()->with('success', 'Akun berhasil dibuat.');
    }

    public function updateRole(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'role' => ['required', 'in:' . implode(',', array_keys(User::$roleLabel))],
        ]);
        if ($user->is($request->user()) && $data['role'] !== User::ROLE_ADMIN) {
            return back()->with('error', 'Role akun admin yang sedang digunakan tidak dapat diubah.');
        }

        $user->update($data);

        return back()->with('success', 'Role akun diperbarui.');
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        if ($user->is($request->user())) {
            return back()->with('error', 'Akun admin yang sedang digunakan tidak dapat dinonaktifkan.');
        }

        $user->update(['status_akun' => $user->status_akun === 'aktif' ? 'nonaktif' : 'aktif']);

        return back()->with('success', 'Status akun diperbarui.');
    }
}
