<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\TeacherGroup;
use App\Models\TeacherProfile;
use App\Models\AssessorProfile;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

class UserController extends Controller
{
    public function index(Request $request)
    {
        $institution = auth()->user()->institution;

        $query = User::with(['roles', 'institution'])
            ->where('institution_id', $institution?->id);

        // Search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function($q) use ($request) {
                $q->where('key', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->whereNull('deactivated_at');
            } else {
                $query->whereNotNull('deactivated_at');
            }
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create()
    {
        $roles = Role::all();
        $teacherGroups = TeacherGroup::where('institution_id', auth()->user()->institution_id)->get();

        return view('admin.users.create', compact('roles', 'teacherGroups'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            // Teacher fields
            'nuptk' => ['nullable', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:50'],
            'rank' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'teacher_group_id' => ['nullable', 'exists:teacher_groups,id'],
            // Assessor fields
            'assessor_type' => ['nullable', 'string', 'in:principal,supervisor,peer'],
        ]);

        $user = User::create([
            'id' => Str::ulid(),
            'institution_id' => auth()->user()->institution_id,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'email_verified_at' => now(),
        ]);

        $user->roles()->attach($validated['roles']);

        // Check if teacher role is selected
        $teacherRole = Role::where('key', 'teacher')->first();
        if ($teacherRole && in_array($teacherRole->id, $validated['roles'])) {
            TeacherProfile::create([
                'id' => Str::ulid(),
                'user_id' => $user->id,
                'teacher_group_id' => $validated['teacher_group_id'] ?? null,
                'nuptk' => $validated['nuptk'] ?? null,
                'nip' => $validated['nip'] ?? null,
                'rank' => $validated['rank'] ?? null,
                'position' => $validated['position'] ?? null,
            ]);
        }

        // Check if assessor role is selected
        $assessorRole = Role::where('key', 'assessor')->first();
        if ($assessorRole && in_array($assessorRole->id, $validated['roles'])) {
            AssessorProfile::create([
                'id' => Str::ulid(),
                'user_id' => $user->id,
                'assessor_type' => $validated['assessor_type'] ?? 'peer',
            ]);
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'create_user',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Created user: {$user->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $roles = Role::all();
        $teacherGroups = TeacherGroup::where('institution_id', auth()->user()->institution_id)->get();
        $user->load(['roles', 'teacherProfile', 'assessorProfile']);

        return view('admin.users.edit', compact('user', 'roles', 'teacherGroups'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,' . $user->id],
            'roles' => ['required', 'array'],
            'roles.*' => ['exists:roles,id'],
            // Teacher fields
            'nuptk' => ['nullable', 'string', 'max:50'],
            'nip' => ['nullable', 'string', 'max:50'],
            'rank' => ['nullable', 'string', 'max:100'],
            'position' => ['nullable', 'string', 'max:100'],
            'teacher_group_id' => ['nullable', 'exists:teacher_groups,id'],
            // Assessor fields
            'assessor_type' => ['nullable', 'string', 'in:principal,supervisor,peer'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
        ]);

        $user->roles()->sync($validated['roles']);

        // Handle teacher profile
        $teacherRole = Role::where('key', 'teacher')->first();
        if ($teacherRole && in_array($teacherRole->id, $validated['roles'])) {
            $user->teacherProfile()->updateOrCreate(
                ['user_id' => $user->id],
                [
                    'teacher_group_id' => $validated['teacher_group_id'] ?? null,
                    'nuptk' => $validated['nuptk'] ?? null,
                    'nip' => $validated['nip'] ?? null,
                    'rank' => $validated['rank'] ?? null,
                    'position' => $validated['position'] ?? null,
                ]
            );
        }

        // Handle assessor profile
        $assessorRole = Role::where('key', 'assessor')->first();
        if ($assessorRole && in_array($assessorRole->id, $validated['roles'])) {
            $user->assessorProfile()->updateOrCreate(
                ['user_id' => $user->id],
                ['assessor_type' => $validated['assessor_type'] ?? 'peer']
            );
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'update_user',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Updated user: {$user->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Data pengguna berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $userName = $user->name;
        $user->delete();

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'delete_user',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Deleted user: {$userName}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return redirect()->route('admin.users.index')
            ->with('success', 'Pengguna berhasil dihapus.');
    }

    public function resetPassword(Request $request, User $user)
    {
        $validated = $request->validate([
            'password' => ['required', 'confirmed', Password::defaults()],
        ]);

        $user->update([
            'password' => Hash::make($validated['password']),
        ]);

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => 'reset_password',
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "Reset password for user: {$user->name}",
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
        ]);

        return back()->with('success', 'Password berhasil direset.');
    }

    public function toggleStatus(User $user)
    {
        if ($user->deactivated_at) {
            $user->update(['deactivated_at' => null]);
            $action = 'activate_user';
            $message = 'Pengguna berhasil diaktifkan.';
        } else {
            $user->update(['deactivated_at' => now()]);
            $action = 'deactivate_user';
            $message = 'Pengguna berhasil dinonaktifkan.';
        }

        // Log activity
        ActivityLog::create([
            'id' => Str::ulid(),
            'user_id' => auth()->id(),
            'action' => $action,
            'entity_type' => User::class,
            'entity_id' => $user->id,
            'description' => "{$action} for user: {$user->name}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        return back()->with('success', $message);
    }
}
