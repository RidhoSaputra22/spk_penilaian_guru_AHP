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

        $query = User::with(['roles'])
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
        $institution = auth()->user()->institution;
        $teacherGroups = TeacherGroup::where('institution_id', $institution?->id)->get();

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
            'employee_no' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:100'],
            'teacher_group_ids' => ['nullable', 'array'],
            'teacher_group_ids.*' => ['exists:teacher_groups,id'],
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
            $teacherProfile = TeacherProfile::create([
                'id' => Str::ulid(),
                'user_id' => $user->id,
                'employee_no' => $validated['employee_no'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'employment_status' => $validated['employment_status'] ?? null,
                'position' => $validated['position'] ?? null,
            ]);

            // Attach teacher to groups (many-to-many)
            if (!empty($validated['teacher_group_ids'])) {
                $teacherProfile->groups()->attach($validated['teacher_group_ids']);
            }
        }

        // Check if assessor role is selected
        $assessorRole = Role::where('key', 'assessor')->first();
        if ($assessorRole && in_array($assessorRole->id, $validated['roles'])) {
            AssessorProfile::create([
                'id' => Str::ulid(),
                'user_id' => $user->id,
                'meta' => [
                    'type' => $validated['assessor_type'] ?? 'peer',
                ],
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

    public function show(User $user)
    {
        // Simplified show method
        return view('admin.users.show', compact('user'));
    }

    public function edit(User $user)
    {
        // Simplified edit method
        $roles = [];
        $teacherGroups = [];

        return view('admin.users.edit', compact('user', 'roles', 'teacherGroups'));
    }

    public function update(Request $request, User $user)
    {
        // Simplified update - just basic fields, make roles optional
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255'],
            'roles' => ['nullable', 'array'],
        ]);

        $user->update([
            'name' => $validated['name'],
            'email' => $validated['email'],
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
        // Generate new password (could be random or default)
        $newPassword = 'password123'; // Default password

        $user->update([
            'password' => Hash::make($newPassword),
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
