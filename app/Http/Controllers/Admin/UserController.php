<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Models\AssessorProfile;
use App\Models\Role;
use App\Models\TeacherGroup;
use App\Models\TeacherProfile;
use App\Models\User;
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
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            });
        }

        // Role filter
        if ($request->filled('role')) {
            $query->whereHas('roles', function ($q) use ($request) {
                $q->where('key', $request->role);
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $users = $query->latest()->paginate(10)->withQueryString();
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    public function create(Request $request)
    {
        $roles = Role::all();
        $institution = auth()->user()->institution;
        $teacherGroups = TeacherGroup::where('institution_id', $institution?->id)->get();

        return view('admin.users.create', compact('roles', 'teacherGroups', 'request'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'password' => ['required', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:admin,assessor,teacher'],
            'status' => ['required', 'string', 'in:active,inactive'],
            // Teacher fields
            'employee_no' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['nullable', 'string', 'max:50'],
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
            'status' => $validated['status'],
        ]);

        // Find and attach role
        $role = Role::where('key', $validated['role'])->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        // Check if teacher role is selected
        if ($validated['role'] === 'teacher') {
            $teacherProfile = TeacherProfile::create([
                'id' => Str::ulid(),
                'user_id' => $user->id,
                'employee_no' => $validated['employee_no'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'employment_status' => $validated['employment_status'] ?? null,
                'position' => $validated['position'] ?? null,
            ]);

            // Attach teacher to group if selected
            if (! empty($validated['teacher_group_id'])) {
                $teacherProfile->groups()->attach($validated['teacher_group_id']);
            }
        }

        // Check if assessor role is selected
        if ($validated['role'] === 'assessor') {
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
        $user->load(['roles', 'teacherProfile.groups', 'assessorProfile']);
        $roles = Role::all();
        $institution = auth()->user()->institution;
        $teacherGroups = TeacherGroup::where('institution_id', $institution?->id)->get();

        return view('admin.users.edit', compact('user', 'roles', 'teacherGroups'));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email,'.$user->id],
            'password' => ['nullable', 'confirmed', Password::defaults()],
            'role' => ['required', 'string', 'in:admin,assessor,teacher'],
            'status' => ['required', 'string', 'in:active,inactive'],
            // Teacher fields
            'employee_no' => ['nullable', 'string', 'max:50'],
            'subject' => ['nullable', 'string', 'max:100'],
            'employment_status' => ['nullable', 'string', 'max:50'],
            'position' => ['nullable', 'string', 'max:100'],
            'teacher_group_id' => ['nullable', 'exists:teacher_groups,id'],
            // Assessor fields
            'assessor_type' => ['nullable', 'string', 'in:principal,supervisor,peer'],
        ]);

        // Update basic fields
        $updateData = [
            'name' => $validated['name'],
            'email' => $validated['email'],
            'status' => $validated['status'],
        ];

        // Update password if provided
        if ($request->filled('password')) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);

        // Update role if changed
        $newRole = Role::where('key', $validated['role'])->first();
        if ($newRole) {
            $user->roles()->sync([$newRole->id]);
        }

        // Handle profile updates based on role
        if ($validated['role'] === 'teacher') {
            $teacherProfile = $user->teacherProfile;
            if (! $teacherProfile) {
                $teacherProfile = TeacherProfile::create([
                    'id' => Str::ulid(),
                    'user_id' => $user->id,
                ]);
            }

            $teacherProfile->update([
                'employee_no' => $validated['employee_no'] ?? null,
                'subject' => $validated['subject'] ?? null,
                'employment_status' => $validated['employment_status'] ?? null,
                'position' => $validated['position'] ?? null,
            ]);

            // Update teacher group if provided
            if (! empty($validated['teacher_group_id'])) {
                $teacherProfile->groups()->sync([$validated['teacher_group_id']]);
            } else {
                $teacherProfile->groups()->detach();
            }
        } elseif ($validated['role'] === 'assessor') {
            $assessorProfile = $user->assessorProfile;
            if (! $assessorProfile) {
                $assessorProfile = AssessorProfile::create([
                    'id' => Str::ulid(),
                    'user_id' => $user->id,
                ]);
            }

            $assessorProfile->update([
                'meta' => [
                    'type' => $validated['assessor_type'] ?? 'peer',
                ],
            ]);
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
        try {
            if ($user->status === 'inactive') {
                $user->update(['status' => 'active']);
                $action = 'activate_user';
                $message = 'Pengguna berhasil diaktifkan.';
            } else {
                $user->update(['status' => 'inactive']);
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
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal mengubah status pengguna: '.$e->getMessage());
        }
    }
}
